<?php

namespace App\Controllers;

use App\Dao\UniverseDao;
use App\Models\UniverseModel;
use App\Exceptions;

class UniverseController extends BaseController {
  private UniverseDao $universeDao;

  /**
   * Constructeur
   */
  public function __construct() {
    $this->universeDao = new UniverseDao();
  }

  /**
   * Récupération d'un univers
   *
   * @param integer $universeId Identifiant de l'univers
   * @return UniverseModel Données de l'univers
   */
  public function getUniverse(int $universeId): UniverseModel {
    $universe = $this->universeDao->findOne($universeId);

    if (!$universe->id) {
      throw new Exceptions\NotFoundException("Univers non trouvé");
    }

    $galaxyController = new GalaxyController();
    $universe->galaxies = $galaxyController->getGalaxiesByUniverse($universe->id);

    return $universe;
  }


  /**
   * Récupération des univers
   *
   * @return UniverseModel[] Liste des univers
   */
  public function getUniverses(): array {
    $universes = $this->universeDao->findAll();

    $galaxyController = new GalaxyController();

    foreach ($universes as &$universe) {
      $universe->galaxies = $galaxyController->getGalaxiesByUniverse($universe->id);
    }

    return $universes;
  }


  /**
   * Création d'un univers
   *
   * @return UniverseModel Données de l'univers
   */
  public function createUniverse(): UniverseModel {
    $universe = new UniverseModel();

    // Génération du nom de l'univers
    $universe->name = $this->randomName(1)[0];

    // Création de l'univers
    $universe = $this->universeDao->insertOne($universe);

    if (!$universe->id) {
      throw new Exceptions\InternalErrorException("Création de l'univers a échouée");
    }

    // Création des galaxies
    $galaxyController = new GalaxyController();
    $universe->galaxies = $galaxyController->createGalaxies($universe->id);

    return $universe;
  }
}
