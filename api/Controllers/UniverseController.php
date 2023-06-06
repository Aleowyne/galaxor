<?php

namespace App\Controllers;

use App\Dao\UniverseDao;
use App\Models\UniverseModel;
use App\Exceptions;

class UniverseController extends BaseController {
  private $universeDao = null;
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
  public function __construct(string $requestMethod = "", array $params = [], array $body = []) {
    $this->universeDao = new UniverseDao();
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
        $universe = $this->getUniverse();
        $this->sendSuccessResponse($universe->toArray());
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
          $universes = $this->getUniverses();

          $arrayUniverses = [
            "universes" => array_map(function (UniverseModel $universe) {
              return $universe->toArray();
            }, $universes)
          ];

          $this->sendSuccessResponse($arrayUniverses);
          break;

          // Création d'un univers
        case "POST":
          $universe = $this->createUniverse();
          $this->sendSuccessResponse($universe->toArray());
          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }

      return;
    }

    throw new Exceptions\NotFoundException("URL non valide");
  }


  /**
   * Récupération d'un univers
   *
   * @param integer $universeId Identifiant de l'univers
   * @return UniverseModel Données de l'univers
   */
  public function getUniverse(int $universeId = 0): UniverseModel {
    $universeId = (int) ($this->params[0] ?? $universeId);

    $universe = $this->universeDao->findOne($universeId);

    if (!$universe->id) {
      throw new Exceptions\NotFoundException("Univers non trouvé");
    }

    $galaxyController = new GalaxyController();
    $universe->galaxies = $galaxyController->getGalaxiesByUniverse($universe->id);

    return $universe;
  }


  /**
   * Récupération des univers
   *
   * @return UniverseModel[] Liste des univers
   */
  public function getUniverses(): array {
    $universes = $this->universeDao->findAll();

    $galaxyController = new GalaxyController();

    foreach ($universes as &$universe) {
      $universe->galaxies = $galaxyController->getGalaxiesByUniverse($universe->id);
    }

    return $universes;
  }


  /**
   * Création d'un univers
   *
   * @return UniverseModel Données de l'univers
   */
  public function createUniverse(): UniverseModel {
    $universe = new UniverseModel();

    // Génération du nom de l'univers
    $universe->name = $this->randomName(1)[0];

    // Création de l'univers
    $universe = $this->universeDao->insertOne($universe);

    if (!$universe->id) {
      throw new Exceptions\InternalErrorException("Création de l'univers a échouée");
    }

    // Création des galaxies
    $galaxyController = new GalaxyController();
    $universe->galaxies = $galaxyController->createGalaxies($universe->id);

    return $universe;
  }
}
