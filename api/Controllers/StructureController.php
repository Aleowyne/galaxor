<?php

namespace App\Controllers;

use App\Dao\StructureDao;
use App\Models\StructureModel;
use App\Exceptions;

class StructureController extends ItemController {
  private StructureDao $structureDao;

  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId = 0) {
    parent::__construct($planetId);
    $this->structureDao = new StructureDao();
  }


  /**
   * Récupération d'une structure d'une planète
   *
   * @param string $structureId Identifiant de la structure
   * @param string $itemType Type de l'item
   * @return StructureModel Données de la structure
   */
  public function getItemPlanet(string $structureId, string $itemType = "STRUCTURE"): StructureModel {
    $item = parent::getItemPlanet($structureId, $itemType);

    $structure = new StructureModel();

    if (!$item->itemId) {
      throw new Exceptions\InternalErrorException("Structure non trouvée");
    }

    foreach ($item as $key => $value) {
      $structure->$key = $value;
    }

    return $structure;
  }


  /**
   * Récupération des structures d'une planète
   *
   * @param string $itemType Type de l'item
   * @return StructureModel[] Liste des structures
   */
  public function getItemsPlanet(string $itemType = "STRUCTURE"): array {
    $structures = $this->structureDao->findAllByPlanet($this->planetId);

    // Evaluation des formules des structures
    return $this->evaluateFormulas($structures);
  }
}
