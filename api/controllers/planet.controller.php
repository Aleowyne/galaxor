<?php

class PlanetController extends BaseController {
  private $planetModel = null;
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
    $this->planetModel = new PlanetModel();
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
    /* Endpoint /api/planets/:id */
    if (preg_match("/\/api\/planets\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getPlanet();
          break;
        case "PUT":
          $this->assignUserPlanet();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets */
    if (preg_match("/\/api\/planets$/", $uri)) {
      $this->getPlanets();
      return;
    }

    /* Endpoint /api/solarsystem/:id/planets */
    if (preg_match("/\/api\/solarsystems\/[0-9]*\/planets$/", $uri)) {
      $this->getPlanetsBySolarSystem();
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }

  /**
   * Récupération d'une planète
   *
   * @return void
   */
  private function getPlanet(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $planetId = ["id" => (int) $this->params[0]];

    $result = $this->planetModel->findOne($planetId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Récupération des planètes
   *
   * @return void
   */
  private function getPlanets(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $result = $this->planetModel->findAll();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Assignation d'un utilisateur à une planète
   *
   * @return void
   */
  private function assignUserPlanet(): void {
    if (!$this->checkMethod($this->requestMethod, ['PUT'])) {
      return;
    }

    // Données de l'utilisateur non valide
    if (!$this->checkUserId($this->body)) {
      $this->invalidBody();
      return;
    }

    $planet = [
      "id" => (int) $this->params[0],
      "user_id" => (int) $this->body["user_id"]
    ];

    $result = $this->planetModel->updateOne($planet);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK");
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Récupération des planètes d'un système solaire
   *
   * @return void
   */
  private function getPlanetsBySolarSystem(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $solarSystemId = ["solar_system_id" => (int) $this->params[0]];

    $result = $this->planetModel->findAllBySolarSystem($solarSystemId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Création de planètes pour plusieurs systèmes solaires
   *
   * @return array Liste des ID des planètes
   */
  public function createPlanets() {
    $planets = [];

    foreach ($this->params as $solarSystemId) {
      $names = $this->randomName(rand(4, 10));

      foreach ($names as $name) {
        array_push(
          $planets,
          [
            "solar_system_id" => $solarSystemId,
            "name" => $name,
            "position" => rand(1, 10)
          ]
        );
      }
    }

    return $this->planetModel->insertMultiples($planets);
  }


  /**
   * Contrôle des données de l'utilisateur 
   *
   * @param array $body Données de l'utilisateur
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserId(array $body): bool {
    return $body["user_id"] ?? false;
  }
}
