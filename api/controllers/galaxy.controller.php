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
   * @return void
   */
  public function processRequest(string $uri): void {
    /* Endpoint /api/galaxies/:id */
    if (preg_match("/\/api\/galaxies\/[0-9]*$/", $uri)) {
      $this->getGalaxy();
      return;
    }

    /* Endpoint /api/universes/:id/galaxies */
    if (preg_match("/\/api\/universes\/[0-9]*\/galaxies$/", $uri)) {
      $this->getGalaxiesByUniverse();
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }

  /**
   * Récupération d'une galaxie
   *
   * @return void
   */
  private function getGalaxy(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $galaxyId = ["id" => (int) $this->params[0]];

    $result = $this->galaxyModel->findOne($galaxyId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Récupération des galaxies d'un univers
   *
   * @return void
   */
  private function getGalaxiesByUniverse(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $universeId = ["universe_id" => (int) $this->params[0]];

    $result = $this->galaxyModel->findAllByUniverse($universeId);

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
    $galaxies = [];
    $universeId = (int) $this->params[0];

    $names = $this->randomName(5);

    foreach ($names as $name) {
      array_push(
        $galaxies,
        [
          "universe_id" => $universeId,
          "name" => $name
        ]
      );
    }

    return $this->galaxyModel->insertMultiples($galaxies);
  }
}
