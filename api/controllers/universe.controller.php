<?php

class UniverseController extends BaseController {
  private $universeModel = null;
  private $requestMethod = "";
  private $params = [];
  private $body = [];

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param array $params Paramètres de la requête
   * @param array $body Contenu de la requête
   */
  public function __construct(string $requestMethod, array $params, array $body) {
    $this->universeModel = new UniverseModel();
    $this->requestMethod = $requestMethod;
    $this->params = $params;
    $this->body = $body;
  }

  /**
   * Traitement de la requête
   *
   * @param string $uri URI
   * @return void
   */
  public function processRequest(string $uri): void {
    /* Endpoint /api/universes/:id */
    if (preg_match("/\/api\/universes\/[0-9]*$/", $uri)) {
      $this->getUniverse();
      return;
    }

    /* Endpoint /api/universes */
    if (preg_match("/\/api\/universes$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getUniverses();
          break;
        case "POST":
          $this->createUniverse();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }

  /**
   * Récupération d'un univers
   *
   * @return void
   */
  private function getUniverse(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $universeId = ["id" => (int) $this->params[0]];

    $result = $this->universeModel->findOne($universeId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Récupération de plusieurs univers
   *
   * @return void
   */
  private function getUniverses(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $result = $this->universeModel->findAll();

    $this->sendResponse("HTTP/1.1 200 OK", $result);
  }

  /**
   * Création d'un univers
   *
   * @return void
   */
  private function createUniverse(): void {
    if (!$this->checkMethod($this->requestMethod, ['POST'])) {
      return;
    }

    // Création de l'univers
    $universeName = ["name" => $this->randomName(1)[0]];
    $universeId = $this->universeModel->insertOne($universeName);

    // Création des galaxies
    $galaxyController = new GalaxyController($this->requestMethod, [$universeId], $this->body);
    $galaxiesId = $galaxyController->createGalaxies();

    // Création des systèmes solaires
    $solarSystemController = new SolarSystemController($this->requestMethod, $galaxiesId, $this->body);
    $solarSystemsId = $solarSystemController->createSolarSystems();

    // Création des planètes
    $planetcontroller = new PlanetController($this->requestMethod, $solarSystemsId, $this->body);
    $planetcontroller->createPlanets();

    if ($universeId != 0) {
      $this->sendResponse("HTTP/1.1 201 Created");
    } else {
      $this->sendResponse("HTTP/1.1 500 Internal Server Error");
    }
  }
}
