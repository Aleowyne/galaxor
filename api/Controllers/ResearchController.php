<?php

namespace App\Controllers;

use App\Models\ResearchModel;
use App\Exceptions;

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
  public function getItemPlanet(string $researchId, string $itemType = "RESEARCH"): ResearchModel {
    $item = parent::getItemPlanet($researchId, $itemType);

    $research = new ResearchModel();

    if (!$item->itemId) {
      throw new Exceptions\InternalErrorException("Recherche non trouvée");
    }

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
  public function getItemsPlanet(string $itemType = "RESEARCH"): array {
    $items = parent::getItemsPlanet($itemType);

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
