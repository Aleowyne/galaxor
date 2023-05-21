<?php

class StructureController extends ItemController {
  private $structureDao = null;

  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId) {
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
  public function getItem(string $structureId, string $itemType = "STRUCTURE"): StructureModel {
    $item = parent::getItem($structureId, $itemType);

    if (!$item->id) {
      $this->sendErrorResponse("Structure non trouvée");
    }

    $structure = new StructureModel();

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
  public function getItems(string $itemType = "STRUCTURE"): array {
    $structures = $this->structureDao->findAllByPlanet($this->planetId);

    // Evaluation des formules des items d'une planète
    return $this->evaluateFormulas($structures);
  }
}
