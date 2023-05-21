<?php

class ResearchController extends ItemController {
  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId) {
    parent::__construct($planetId);
  }


  /**
   * Récupération d'une recherche d'une planète
   * 
   * @param string $researchId Identifiant de la recherche
   * @param string $itemType Type de l'item
   * @return ResearchModel Données de la recherche
   */
  public function getItem(string $researchId, string $itemType = "RESEARCH"): ResearchModel {
    $item = parent::getItem($researchId, $itemType);

    if (!$item->id) {
      $this->sendErrorResponse("Recherche non trouvée");
    }

    $research = new ResearchModel();

    foreach ($item as $key => $value) {
      $research->$key = $value;
    }

    return $research;
  }


  /**
   * Récupération des recherches d'une planète
   * 
   * @param string $itemType Type de l'item
   * @return ResearchModel[] Liste des recherches
   */
  public function getItems(string $itemType = "RESEARCH"): array {
    $items = parent::getItems($itemType);

    foreach ($items as $item) {
      $research = new ResearchModel();

      foreach ($item as $key => $value) {
        $research->$key = $value;
      }

      $researches[] = $research;
    }

    return $researches;
  }
}
