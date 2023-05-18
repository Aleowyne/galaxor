<?php

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
  public function __construct(string $requestMethod, array $params, array $body) {
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
    /* Endpoint /api/galaxies/:id */
    if (preg_match("/\/api\/galaxies\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getGalaxy();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    $this->sendErrorResponse("URL non valide");
  }


  /**
   * Récupération d'une galaxie
   */
  private function getGalaxy(): void {
    $galaxyId = (int) ($this->params[0] ?? 0);

    $galaxy = $this->galaxyDao->findOne($galaxyId);

    if ($galaxy->id) {
      $this->sendSuccessResponse($galaxy->toArray());
    } else {
      $this->sendErrorResponse("Galaxie non trouvée");
    }
  }


  /**
   * Création de galaxies pour un univers
   *
   * @return GalaxyModel[] Liste des galaxies
   */
  public function createGalaxies(): array {
    $universeId = (int) ($this->params[0] ?? 0);

    // Génération du nom des galaxies
    $names = $this->randomName(5);

    $galaxies = $this->galaxyDao->insertMultiples($universeId, $names);

    // Création des systèmes solaires
    return array_map(function (GalaxyModel $galaxy) {
      $solarSystemController = new SolarSystemController($this->requestMethod, [$galaxy->id], $this->body);
      $galaxy->solarSystems = $solarSystemController->createSolarSystems();

      return $galaxy;
    }, $galaxies);
  }
}
