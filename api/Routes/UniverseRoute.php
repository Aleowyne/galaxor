<?php

namespace App\Routes;

use App\Controllers\UniverseController;
use App\Models\UniverseModel;
use App\Exceptions;

class UniverseRoute extends BaseRoute {
  private UniverseController $universeController;
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
    $this->universeController = new UniverseController();
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
    // Endpoint /api/universes/:id
    if (preg_match("/\/api\/universes\/\d+$/", $uri)) {
      // Récupération d'un universe
      if ($this->requestMethod === "GET") {
        $this->requestGetUniverse();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }

      return;
    }

    // Endpoint /api/universes
    if (preg_match("/\/api\/universes$/", $uri)) {
      switch ($this->requestMethod) {
          // Récupération des univers
        case "GET":
          $this->requestGetUniverses();
          break;

          // Création d'un univers
        case "POST":
          $this->requestCreateUniverse();
          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }

      return;
    }

    throw new Exceptions\NotFoundException("URL non valide");
  }


  /**
   * Requête récupération d'un univers
   */
  private function requestGetUniverse(): void {
    $universeId = (int) ($this->params[0]) ?? 0;
    $universe = $this->universeController->getUniverse($universeId);
    $this->sendSuccessResponse($universe->toArray());
  }


  /**
   * Requête récupération d'une liste d'univers
   */
  private function requestGetUniverses(): void {
    $universes = $this->universeController->getUniverses();

    $arrayUniverses = [
      "universes" => array_map(function (UniverseModel $universe) {
        return $universe->toArray();
      }, $universes)
    ];

    $this->sendSuccessResponse($arrayUniverses);
  }


  /**
   * Requête création d'un univers
   */
  private function requestCreateUniverse(): void {
    $universe = $this->universeController->createUniverse();
    $this->sendSuccessResponse($universe->toArray());
  }
}
