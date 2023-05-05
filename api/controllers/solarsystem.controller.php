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
   */
  public function processRequest(string $uri): void {
    /* Endpoint /api/solarsystems/:id */
    if (preg_match("/\/api\/solarsystems\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getSolarSystem();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/galaxies/:id/solarsystems */
    if (preg_match("/\/api\/galaxies\/[0-9]*\/solarsystems$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getSolarSystemsByGalaxy();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }


  /**
   * Récupération d'un système solaire
   */
  private function getSolarSystem(): void {
    $this->solarSystemModel->setId($this->params[0] ?? 0);

    $result = $this->solarSystemModel->findOne();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération des systèmes solaires d'une galaxie
   */
  private function getSolarSystemsByGalaxy(): void {
    $this->solarSystemModel->setGalaxyId($this->params[0] ?? 0);

    $result = $this->solarSystemModel->findAllByGalaxy();

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
    foreach ($this->params as $galaxyId) {
      // Génération du nom des systèmes solaires
      $names = $this->randomName(10);

      foreach ($names as $name) {
        $this->solarSystemModel->addName($name, $galaxyId);
      }
    }

    return $this->solarSystemModel->insertMultiples();
  }
}
