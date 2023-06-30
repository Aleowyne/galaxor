<?php

namespace App\Controllers;

use App\Dao\ItemDao;
use App\Models\ItemModel;
use App\Models\CostModel;
use App\Models\PrerequisiteModel;
use App\Exceptions;
use DateTime;
use DateInterval;

class ItemController extends BaseController {
  private ItemDao $itemDao;
  protected int $planetId;

  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId = 0) {
    $this->itemDao = new ItemDao();
    $this->planetId = $planetId;
  }


  /**
   * Démarrage de l'upgrade d'un item d'une planète
   *
   * @param ItemModel $item Données de l'item
   */
  public function startUpgradeItem(ItemModel $item): void {
    // Vérification si l'item est déjà en cours d'upgrade
    if ($item->upgradeInProgress) {
      throw new Exceptions\InternalErrorException("Upgrade en cours");
    }

    // Contrôle des pré-requis
    if ($item->prerequisites) {
      throw new Exceptions\InternalErrorException("Pré-requis non complétés");
    }

    $currentDate = new DateTime();

    // Détermination de la date de fin de l'upgrade
    $buildTime = new DateInterval("PT" . $item->buildTime . "S");
    $item->endTimeUpgrade = $currentDate->add($buildTime)->format(self::FORMAT_DATE);
    $item->upgradeInProgress = true;

    // Mise à jour de l'item
    $isUpdate = $this->itemDao->updateOneByPlanet($this->planetId, $item);

    if (!$isUpdate) {
      throw new Exceptions\InternalErrorException("Upgrade a échoué");
    }
  }


  /**
   * Finalisation de l'upgrade d'un item d'une planète
   *
   * @param ItemModel $item Données de l'item
   */
  public function finishUpgradeItem(ItemModel $item): void {
    // Vérification si l'item n'est pas en cours d'upgrade
    if (!$item->upgradeInProgress) {
      throw new Exceptions\InternalErrorException("Pas d'upgrade en cours");
    }

    $currentDate = new DateTime();
    $endTimeBuild = new DateTime($item->endTimeUpgrade);

    // Upgrade terminé
    if ($currentDate >= $endTimeBuild) {
      $item->level++;
      $item->upgradeInProgress = false;
    } else {
      throw new Exceptions\InternalErrorException("Upgrade non terminé");
    }

    // Mise à jour de l'item
    $isUpdate = $this->itemDao->updateOneByPlanet($this->planetId, $item);

    if (!$isUpdate) {
      throw new Exceptions\InternalErrorException("Upgrade a échoué");
    }
  }


  /**
   * Réinitialisation des items au niveau 0
   *
   * @param ItemModel[] $items Données des items
   */
  public function resetItems(array $items): void {
    if (!$items) {
      return;
    }

    $currentDate = new DateTime();

    foreach ($items as $item) {
      $item->level = 0;
      $item->upgradeInProgress = false;
      $item->endTimeUpgrade = $currentDate->format(self::FORMAT_DATE);
    }

    $isUpdate = $this->itemDao->updateMultiplesByPlanet($this->planetId, $items);

    if (!$isUpdate) {
      throw new Exceptions\InternalErrorException("Réinitialisation level 1 a échoué");
    }
  }


  /**
   * Récupération d'un item
   *
   * @param string $itemId Identifiant de l'item
   * @param string $itemType Type de l'item
   * @return ItemModel Données de l'item
   */
  protected function getItem(string $itemId, string $itemType): ItemModel {
    $item = $this->itemDao->findOne($itemId, $itemType);

    // Evaluation des formules de l'item d'une planète
    $items = $this->evaluateFormulas([$item]);

    return $items[0];
  }


  /**
   * Récupération d'un item d'une planète
   *
   * @param string $itemId Identifiant de l'item
   * @param string $itemType Type de l'item
   * @return ItemModel Données de l'item
   */
  protected function getItemPlanet(string $itemId, string $itemType): ItemModel {
    $item = $this->itemDao->findOneByPlanet($itemId, $this->planetId, $itemType);

    // Evaluation des formules de l'item d'une planète
    $items = $this->evaluateFormulas([$item]);

    return $items[0];
  }


  /**
   * Récupération des items d'une planète
   *
   * @param string $itemType Type de l'item
   * @return ItemModel[] Liste des items
   */
  protected function getItemsPlanet(string $itemType): array {
    $items = $this->itemDao->findAllByPlanet($this->planetId, $itemType);

    // Evaluation des formules des items d'une planète
    return $this->evaluateFormulas($items);
  }


  /**
   * Evaluation des formules des items d'une planète (durée de construction, coût, pré-requis)
   *
   * @param ItemModel[] $items Données des items
   * @return ItemModel[] Données des items
   */
  protected function evaluateFormulas(array $items): array {
    $allItems = $this->itemDao->findAllByPlanet($this->planetId);
    $formulasCost = $this->itemDao->findCosts();
    $prerequisitesItems = $this->itemDao->findPrerequisites();

    // Récupération des variables pour les formules
    foreach ($allItems as $item) {
      $variable = strtolower($item->itemId);
      $$variable = $item->level;
    }

    foreach ($items as &$item) {
      $level = $item->level;

      // Calcul de la durée de construction
      $formula = self::RETURN_STMT . $item->buildTime . ";";
      $item->buildTime = (int) round(eval($formula));

      // Calcul des points d'attaque
      $formula = self::RETURN_STMT . $item->attackPoint . ";";
      $item->attackPoint = (int) round(eval($formula));

      // Calcul des points de défense
      $formula = self::RETURN_STMT . $item->defensePoint . ";";
      $item->defensePoint = (int) round(eval($formula));

      // Calcul de la capacité de transport de ressources
      $formula = self::RETURN_STMT . $item->freightCapacity . ";";
      $item->freightCapacity = (int) round(eval($formula));

      // Récupération du coût de l'item pour prendre un niveau
      $item->costs = array_values(
        array_filter($formulasCost, function (CostModel $formulaCost) use ($item) {
          return $formulaCost->itemId === $item->itemId;
        })
      );

      // Calcul des coûts
      foreach ($item->costs as &$cost) {
        $formula = self::RETURN_STMT . $cost->quantity . ";";
        $cost->quantity = (int) round(eval($formula));
      }

      if ($item->level === 0) {
        // Récupération des pré-requis pour construire l'item
        $prerequisites = array_values(
          array_filter($prerequisitesItems, function (PrerequisiteModel $prerequisite) use ($item) {
            return $prerequisite->itemId === $item->itemId;
          })
        );

        // Récupération des pré-requis non validés
        foreach ($prerequisites as $prerequisite) {
          $variable = strtolower($prerequisite->requiredItemId);

          if (isset($$variable) && $$variable < $prerequisite->level) {
            $item->prerequisites[] = $prerequisite;
          }
        }
      }
    }

    return $items;
  }
}
