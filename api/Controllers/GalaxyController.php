<?php

namespace App\Controllers;

use App\Dao\GalaxyDao;
use App\Models\GalaxyModel;
use App\Exceptions;

class GalaxyController extends BaseController {
  private $galaxyDao = null;
  private $requestMethod = "";
  private $params = [];
  private $body = [];

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param mixed[] $params Paramètres de la requête
   * @param mixed[] $body Contenu de la requête
   */
  public function __construct(string $requestMethod = "", array $params = [], array $body = []) {
    $this->galaxyDao = new GalaxyDao();
    $this->requestMethod = $requestMethod;
    $this->params = $params;
    $this->body = $body;
  }


  /**
   * Traitement de la requête
   *
   * @param string $uri URI
   */
  public function processRequest(string $uri): void {
    // Endpoint /api/galaxies/:id
    if (preg_match("/\/api\/galaxies\/\d*$/", $uri)) {
      // Récupération d'une galaxie
      if ($this->requestMethod === "GET") {
        $galaxy = $this->getGalaxy();
        $this->sendSuccessResponse($galaxy->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }

      return;
    }

    throw new Exceptions\NotFoundException("URL non valide");
  }


  /**
   * Récupération d'une galaxie
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @return GalaxyModel Données de la galaxie
   */
  public function getGalaxy(int $galaxyId = 0): GalaxyModel {
    $galaxyId = (int) ($this->params[0] ?? $galaxyId);

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
  public function createGalaxies(int $universeId = 0): array {
    $universeId = (int) ($this->params[0] ?? $universeId);

    // Génération du nom des galaxies
    $names = $this->randomName(5);

    // Création des galaxies
    $galaxies = $this->galaxyDao->insertMultiples($universeId, $names);

    $solarSystemController = new SolarsystemController();

    // Création des systèmes solaires
    return array_map(function (GalaxyModel $galaxy) use ($solarSystemController) {
      $galaxy->solarSystems = $solarSystemController->createSolarSystems($galaxy->id);

      return $galaxy;
    }, $galaxies);
  }
}
