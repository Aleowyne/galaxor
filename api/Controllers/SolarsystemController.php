<?php

namespace App\Controllers;

use App\Dao\SolarsystemDao;
use App\Models\SolarsystemModel;
use App\Exceptions;

class SolarsystemController extends BaseController {
  private SolarsystemDao $solarSystemDao;

  /**
   * Constructeur
   */
  public function __construct() {
    $this->solarSystemDao = new SolarsystemDao();
  }


  /**
   * Récupération d'un système solaire
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @return SolarsystemModel Données du système solaire
   */
  public function getSolarSystem(int $solarSystemId): SolarsystemModel {
    $solarSystem = $this->solarSystemDao->findOne($solarSystemId);

    if (!$solarSystem->id) {
      throw new Exceptions\NotFoundException("Système solaire non trouvé");
    }

    $planetController = new PlanetController();
    $solarSystem->planets = $planetController->getPlanetsBySolarSystem($solarSystem->id);

    return $solarSystem;
  }


  /**
   * Récupération des systèmes solaires d'une galaxie
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @return SolarsystemModel[] Liste des systèmes solaires
   */
  public function getSolarSystemsByGalaxy(int $galaxyId): array {
    $solarSystems = $this->solarSystemDao->findAllByGalaxy($galaxyId);

    $planetController = new PlanetController();

    foreach ($solarSystems as &$solarSystem) {
      $solarSystem->planets = $planetController->getPlanetsBySolarSystem($solarSystem->id);
    }

    return $solarSystems;
  }


  /**
   * Création de systèmes solaires pour plusieurs galaxies
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @return SolarsystemModel[] Liste des systèmes solaires
   */
  public function createSolarSystems(int $galaxyId): array {
    // Génération du nom des systèmes solaires
    $names = $this->randomName(10);

    // Création des systèmes solaires
    $solarSystems = $this->solarSystemDao->insertMultiplesByGalaxy($galaxyId, $names);

    $planetController = new PlanetController();

    // Création des planètes
    return array_map(function (SolarsystemModel $solarSystem) use ($planetController) {
      $solarSystem->planets = $planetController->createPlanets($solarSystem->id);

      return $solarSystem;
    }, $solarSystems);
  }
}
