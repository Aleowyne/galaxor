<?php

class SolarSystemController extends BaseController {
  private $solarSystemModel = null;
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
    $this->solarSystemModel = new SolarSystemModel();
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
    /* Endpoint /api/solarsystems/:id */
    if (preg_match("/\/api\/solarsystems\/[0-9]*$/", $uri)) {
      $this->getSolarSystem();
      return;
    }

    /* Endpoint /api/galaxies/:id/solarsystems */
    if (preg_match("/\/api\/galaxies\/[0-9]*\/solarsystems$/", $uri)) {
      $this->getSolarSystemsFromGalaxy();
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }

  /**
   * Récupération d'un système solaire
   *
   * @return void
   */
  private function getSolarSystem(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $solarSystemId = ["id" => (int) $this->params[0]];

    $result = $this->solarSystemModel->findOne($solarSystemId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Récupération des systèmes solaires d'une galaxie
   *
   * @return void
   */
  private function getSolarSystemsFromGalaxy(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $galaxyId = ["galaxy_id" => (int) $this->params[0]];

    $result = $this->solarSystemModel->findAllFromGalaxy($galaxyId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Création de systèmes solaires pour plusieurs galaxies
   *
   * @return array Liste des ID des systèmes solaires
   */
  public function createSolarSystems(): array {
    $solarSystems = [];

    foreach ($this->params as $galaxyId) {
      $names = $this->randomName(10);

      foreach ($names as $name) {
        array_push(
          $solarSystems,
          [
            "galaxy_id" => $galaxyId,
            "name" => $name
          ]
        );
      }
    }

    return $this->solarSystemModel->insertMultiples($solarSystems);
  }
}
