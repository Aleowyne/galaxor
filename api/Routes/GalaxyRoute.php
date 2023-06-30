<?php

namespace App\Routes;

use App\Controllers\GalaxyController;
use App\Exceptions;

class GalaxyRoute extends BaseRoute {
  private GalaxyController $galaxyController;
  private string $requestMethod;
  private array $params;
  private array $body;

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param mixed[] $params Paramètres de la requête
   * @param mixed[] $body Contenu de la requête
   */
  public function __construct(string $requestMethod = "", array $params = [], array $body = []) {
    $this->galaxyController = new GalaxyController();
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
        $this->requestGetGalaxy();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }

      return;
    }

    throw new Exceptions\NotFoundException("URL non valide");
  }


  /**
   * Requête récupération d'une galaxie
   */
  private function requestGetGalaxy(): void {
    $galaxyId = (int) ($this->params[0]) ?? 0;
    $galaxy = $this->galaxyController->getGalaxy($galaxyId);
    $this->sendSuccessResponse($galaxy->toArray());
  }
}
