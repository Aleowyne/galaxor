<?php

class GalaxyController extends BaseController {
  private $galaxyModel = null;
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
    $this->galaxyModel = new GalaxyModel();
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
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/universes/:id/galaxies */
    if (preg_match("/\/api\/universes\/[0-9]*\/galaxies$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getGalaxiesByUniverse();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }


  /**
   * Récupération d'une galaxie
   */
  private function getGalaxy(): void {
    $this->galaxyModel->setId($this->params[0] ?? 0);

    $result = $this->galaxyModel->findOne();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération des galaxies d'un univers
   */
  private function getGalaxiesByUniverse(): void {
    $this->galaxyModel->setUniverseId($this->params[0] ?? 0);

    $result = $this->galaxyModel->findAllByUniverse();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Création de galaxies pour un univers
   *
   * @return array Liste des ID des galaxies
   */
  public function createGalaxies(): array {
    $this->galaxyModel->setUniverseId($this->params[0] ?? 0);

    // Génération du nom des galaxies
    $names = $this->randomName(5);

    foreach ($names as $name) {
      $this->galaxyModel->addName($name);
    }

    return $this->galaxyModel->insertMultiples();
  }
}
