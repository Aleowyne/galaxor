<?php

class ItemController extends BaseController {
  private $itemDao = null;
  protected $planetId = 0;

  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId) {
    $this->itemDao = new ItemDao();
    $this->planetId = $planetId;
  }


  /**
   * Démarrage de l'upgrade d'un item d'une planète
   * 
   * @param ItemModel $item Données de l'item
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function startUpgradeItem(ItemModel $item): bool {
    // Contrôle si un upgrade est déjà en cours sur l'item
    if ($item->upgradeInProgress) {
      $this->sendErrorResponse("Upgrade en cours");
      return false;
    }

    // Contrôle des pré-requis
    if ($item->prerequisites) {
      $this->sendErrorResponse("Pré-requis non complétés");
      return false;
    }

    $currentDate = new DateTime();

    // Détermination de la date de fin de l'upgrade
    $buildTime = new DateInterval("PT" . $item->buildTime . "S");
    $item->timeEndUpgrade = $currentDate->add($buildTime)->format("Y-m-d H:i:s");
    $item->upgradeInProgress = 1;

    $isUpdate = $this->itemDao->updateOne($this->planetId, $item);

    if (!$isUpdate) {
      $this->sendErrorResponse("Upgrade échoué");
    };

    return $isUpdate;
  }


  /**
   * Finalisation de l'upgrade d'un item d'une planète
   * 
   * @param ItemModel $item Données de l'item
   * @param boolean Flag indiquant si la mise à jour a réussi
   */
  public function finishUpgradeItem(ItemModel $item): bool {
    // Contrôle si l'upgrade est bien en cours sur l'item
    if (!$item->upgradeInProgress) {
      $this->sendErrorResponse("Pas d'upgrade en cours");
      return false;
    }

    $currentDate = new DateTime();
    $endBuildDate = new DateTime($item->timeEndUpgrade);

    // Upgrade terminé
    if ($currentDate >= $endBuildDate) {
      $item->level++;
      $item->upgradeInProgress = 0;
    } else {
      $this->sendErrorResponse("Upgrade non terminé");
      return false;
    }

    $isUpdate = $this->itemDao->updateOne($this->planetId, $item);

    if (!$isUpdate) {
      $this->sendErrorResponse("Upgrade échoué");
    };

    return $isUpdate;
  }


  /**
   * Récupération d'un item d'une planète
   * 
   * @param string $itemId Identifiant de l'item
   * @param string $itemType Type de l'item
   * @return ItemModel Données de l'item
   */
  protected function getItem(string $itemId, string $itemType): ItemModel {
    $item = $this->itemDao->findOneByPlanet($itemId, $this->planetId, $itemType);

    // Evaluation des formules de l'item d'une planète
    $items = $this->evaluateFormulas([$item]);

    return $items[0] ?? new ItemModel();
  }


  /**
   * Récupération des items d'une planète
   * 
   * @param string $itemType Type de l'item
   * @return ItemModel[] Liste des items
   */
  protected function getItems(string $itemType): array {
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
      $variable = strtolower($item->id);
      $$variable = $item->level;
    }

    foreach ($items as &$item) {
      // Calcul de la durée de construction
      $formula = "return " . $item->buildTime . ";";
      $level = $item->level;

      $item->buildTime = (int) round(eval($formula));

      // Récupération du coût de l'item pour prendre un niveau
      $item->costs = array_values(
        array_filter($formulasCost, function (CostModel $formulaCost) use ($item) {
          return ($formulaCost->itemId === $item->id);
        })
      );

      // Calcul des coûts
      foreach ($item->costs as &$cost) {
        $formula = "return " . $cost->quantity . ";";
        $cost->quantity = (int) round(eval($formula));
      }

      if ($item->level === 0) {
        // Récupération des pré-requis pour construire l'item
        $prerequisites = array_values(
          array_filter($prerequisitesItems, function (PrerequisiteModel $prerequisite) use ($item) {
            return $prerequisite->itemId === $item->id;
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
