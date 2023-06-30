<?php

namespace App\Controllers;

use App\Dao\UnitDao;
use App\Models\UnitModel;
use App\Exceptions;
use DateTime;
use DateInterval;

class UnitController extends ItemController {
  private UnitDao $unitDao;

  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId = 0) {
    parent::__construct($planetId);
    $this->unitDao = new UnitDao();
  }


  /**
   * Récupération d'une unité d'une planète
   *
   * @param string $itemId Identifiant de l'item
   * @param string $itemType Type de l'item
   * @return UnitModel Données de la structure
   */
  public function getItem(string $itemId, string $itemType = "UNIT"): UnitModel {
    // Récupération des informations de l'unité
    $item = parent::getItem($itemId, $itemType);

    $unit = new UnitModel();

    if (!$item->itemId) {
      throw new Exceptions\InternalErrorException("Unité non trouvée");
    }

    foreach ($item as $key => $value) {
      $unit->$key = $value;
    }

    return $unit;
  }


  /**
   * Récupération d'une unité d'une planète
   *
   * @param integer $unitId Identifiant de l'unité
   * @param string $itemType Type de l'item
   * @return UnitModel Données de l'unité
   */
  public function getUnitPlanet(int $unitId): UnitModel {
    $unit = $this->unitDao->findOne($unitId);

    if (!$unit->itemId) {
      throw new Exceptions\InternalErrorException("Unité non trouvée");
    }

    // Evaluation des formules de l'item d'une planète
    $units = $this->evaluateFormulas([$unit]);

    return $units[0];
  }


  /**
   * Récupération des unités d'une planète
   *
   * @param string $itemType Type de l'item
   * @return StructureModel[] Liste des unités
   */
  public function getUnitsPlanet(): array {
    $units = $this->unitDao->findAllByPlanet($this->planetId);

    // Evaluation des formules des unités
    return $this->evaluateFormulas($units);
  }


  /**
   * Création d'une unité sur une planète
   *
   * @param UnitModel $unit Données de l'unité
   * @return UnitModel Données de l'unité
   */
  public function startCreateUnitPlanet(UnitModel $unit): UnitModel {

    // Contrôle des pré-requis
    if ($unit->prerequisites) {
      throw new Exceptions\InternalErrorException("Pré-requis non complétés");
    }

    $currentDate = new DateTime();

    // Détermination de la date de fin de création
    $buildTime = new DateInterval("PT" . $unit->buildTime . "S");
    $unit->endTimeCreate = $currentDate->add($buildTime)->format(self::FORMAT_DATE);
    $unit->createInProgress = true;

    // Ajout de l'unité sur la planète
    $unit->id = $this->unitDao->insertOneByPlanet($this->planetId, $unit);

    if (!$unit->id) {
      throw new Exceptions\InternalErrorException("Création de l'unité a échoué");
    }

    return $unit;
  }


  /**
   * Finalisation de la création d'une unité sur une planète
   *
   * @param UnitModel $unit Données de l'unité
   * @return UnitModel Données de l'unité
   */
  public function finishCreateUnitPlanet(UnitModel $unit): UnitModel {
    // Vérification si l'unité est déjà créée
    if (!$unit->createInProgress) {
      throw new Exceptions\InternalErrorException("Unité déjà créée");
    }

    $currentDate = new DateTime();
    $endTimeCreate = new DateTime($unit->endTimeCreate);

    // Création terminée
    if ($currentDate >= $endTimeCreate) {
      $unit->createInProgress = false;
    } else {
      throw new Exceptions\InternalErrorException("Création non terminée");
    }

    // Mise à jour de l'unité
    $isCreate = $this->unitDao->updateOneByPlanet($this->planetId, $unit);

    if (!$isCreate) {
      throw new Exceptions\InternalErrorException("Création de l'unité a échoué");
    }
    return $unit;
  }


  /**
   * Désactivation des unités
   *
   * @param UnitModel[] $units Données des unités
   */
  public function disableUnits(array $units): void {
    if (!$units) {
      return;
    }

    $isUpdate = $this->unitDao->deactivateMultiples($units);

    if (!$isUpdate) {
      throw new Exceptions\InternalErrorException("Désactivation des unités a échoué");
    }
  }
}
