<?php

class SolarSystemController extends BaseController {
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
  public function __construct(string $requestMethod, array $params, array $body) {
    $this->solarSystemDao = new SolarSystemDao();
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
    if (preg_match("/\/api\/solarsystems\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getSolarSystem();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    $this->sendErrorResponse("URL non valide");
  }


  /**
   * Récupération d'un système solaire
   */
  private function getSolarSystem(): void {
    $solarSystemId = (int) ($this->params[0] ?? 0);

    $solarSystem = $this->solarSystemDao->findOne($solarSystemId);

    if ($solarSystem->id) {
      $this->sendSuccessResponse($solarSystem->toArray());
    } else {
      $this->sendErrorResponse("Système solaire non trouvé");
    }
  }


  /**
   * Création de systèmes solaires pour plusieurs galaxies
   *
   * @return SolarSystemModel[] Liste des systèmes solaires
   */
  public function createSolarSystems(): array {
    $galaxyId = (int) ($this->params[0] ?? 0);

    // Génération du nom des systèmes solaires
    $names = $this->randomName(10);

    $solarSystems = $this->solarSystemDao->insertMultiples($galaxyId, $names);

    // Création des planètes
    return array_map(function (SolarSystemModel $solarSystem) {
      $planetController = new PlanetController($this->requestMethod, [$solarSystem->id], $this->body);
      $solarSystem->planets = $planetController->createPlanets();

      return $solarSystem;
    }, $solarSystems);
  }
}
