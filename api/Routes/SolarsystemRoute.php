<?php

namespace App\Routes;

use App\Controllers\SolarsystemController;
use App\Exceptions;

class SolarsystemRoute extends BaseRoute {
  private SolarsystemController $solarSystemController;
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
    $this->solarSystemController = new SolarsystemController();
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
    // Endpoint /api/solarsystems/:id
    if (preg_match("/\/api\/solarsystems\/\d*$/", $uri)) {
      // Récupération d'uns système solaire
      if ($this->requestMethod === "GET") {
        $this->requestGetSolarSystem();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }

      return;
    }

    throw new Exceptions\NotFoundException("URL non valide");
  }


  /**
   * Requête récupération d'un système solaire
   */
  private function requestGetSolarSystem(): void {
    $solarSystemId = (int) ($this->params[0]) ?? 0;
    $solarSystem = $this->solarSystemController->getSolarSystem($solarSystemId);
    $this->sendSuccessResponse($solarSystem->toArray());
  }
}
