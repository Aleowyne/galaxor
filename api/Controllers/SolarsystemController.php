<?php

namespace App\Controllers;

use App\Dao\SolarsystemDao;
use App\Models\SolarsystemModel;
use App\Exceptions;

class SolarsystemController extends BaseController {
  private $solarSystemDao = null;
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
    $this->solarSystemDao = new SolarsystemDao();
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
    /* Endpoint /api/solarsystems/:id */
    if (preg_match("/\/api\/solarsystems\/\d*$/", $uri)) {
      // Récupération d'un système solaire
      if ($this->requestMethod === "GET") {
        $solarSystem = $this->getSolarSystem();
        $this->sendSuccessResponse($solarSystem->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
      return;
    }

    throw new Exceptions\NotFoundException("URL non valide");
  }


  /**
   * Récupération d'un système solaire
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @return SolarsystemModel Données du système solaire
   */
  public function getSolarSystem(int $solarSystemId = 0): SolarsystemModel {
    $solarSystemId = (int) ($this->params[0] ?? $solarSystemId);

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
  public function createSolarSystems(int $galaxyId = 0): array {
    $galaxyId = (int) ($this->params[0] ?? $galaxyId);

    // Génération du nom des systèmes solaires
    $names = $this->randomName(10);

    // Création des systèmes solaires
    $solarSystems = $this->solarSystemDao->insertMultiples($galaxyId, $names);

    $planetController = new PlanetController();

    // Création des planètes
    return array_map(function (SolarsystemModel $solarSystem) use ($planetController) {
      $solarSystem->planets = $planetController->createPlanets($solarSystem->id);

      return $solarSystem;
    }, $solarSystems);
  }
}
