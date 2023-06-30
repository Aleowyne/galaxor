<?php

namespace App\Controllers;

use App\Dao\GalaxyDao;
use App\Models\GalaxyModel;
use App\Exceptions;

class GalaxyController extends BaseController {
  private GalaxyDao $galaxyDao;

  /**
   * Constructeur
   */
  public function __construct() {
    $this->galaxyDao = new GalaxyDao();
  }


  /**
   * Récupération d'une galaxie
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @return GalaxyModel Données de la galaxie
   */
  public function getGalaxy(int $galaxyId): GalaxyModel {
    $galaxy = $this->galaxyDao->findOne($galaxyId);

    if (!$galaxy->id) {
      throw new Exceptions\NotFoundException("Galaxie non trouvée");
    }

    $solarSystemController = new SolarsystemController();
    $galaxy->solarSystems = $solarSystemController->getSolarSystemsByGalaxy($galaxy->id);

    return $galaxy;
  }


  /**
   * Récupération des galaxies d'un univers
   *
   * @param integer $universeId Identifiant de l'univers
   * @return GalaxyModel[] Liste des galaxies
   */
  public function getGalaxiesByUniverse(int $universeId): array {
    $galaxies = $this->galaxyDao->findAllByUniverse($universeId);

    $solarSystemController = new SolarsystemController();

    foreach ($galaxies as &$galaxy) {
      $galaxy->solarSystems = $solarSystemController->getSolarSystemsByGalaxy($galaxy->id);
    }

    return $galaxies;
  }


  /**
   * Création de galaxies pour un univers
   *
   * @param integer $universeId Identifiant de l'univers
   * @return GalaxyModel[] Liste des galaxies
   */
  public function createGalaxies(int $universeId): array {
    // Génération du nom des galaxies
    $names = $this->randomName(5);

    // Création des galaxies
    $galaxies = $this->galaxyDao->insertMultiplesByUniverse($universeId, $names);

    $solarSystemController = new SolarsystemController();

    // Création des systèmes solaires
    return array_map(function (GalaxyModel $galaxy) use ($solarSystemController) {
      $galaxy->solarSystems = $solarSystemController->createSolarSystems($galaxy->id);

      return $galaxy;
    }, $galaxies);
  }
}
