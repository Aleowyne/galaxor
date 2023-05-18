<?php

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
  public function __construct(string $requestMethod, array $params, array $body) {
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
    /* Endpoint /api/universes/:id */
    if (preg_match("/\/api\/universes\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getUniverse();
          break;
        default:
          $this->sendMethodNotSupported();
      }
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
          $this->sendMethodNotSupported();
      }
      return;
    }

    $this->sendErrorResponse("URL non valide");
  }


  /**
   * Récupération d'un univers
   */
  private function getUniverse(): void {
    $universeId = (int) ($this->params[0] ?? 0);

    $universe = $this->universeDao->findOne($universeId);

    if ($universe->id) {
      $this->sendSuccessResponse($universe->toArray());
    } else {
      $this->sendErrorResponse("Univers non trouvé");
    }
  }


  /**
   * Récupération de plusieurs univers
   */
  private function getUniverses(): void {
    $universes = $this->universeDao->findAll();

    $arrayUniverses = [
      "universes" => array_map(function (UniverseModel $universe) {
        return $universe->toArray();
      }, $universes)
    ];

    $this->sendSuccessResponse($arrayUniverses);
  }


  /**
   * Création d'un univers
   */
  private function createUniverse(): void {
    $universe = new UniverseModel();

    // Génération du nom de l'univers
    $universe->name = $this->randomName(1)[0];

    // Création de l'univers
    $universe = $this->universeDao->insertOne($universe);

    // Création des galaxies
    $galaxyController = new GalaxyController($this->requestMethod, [$universe->id], $this->body);
    $universe->galaxies = $galaxyController->createGalaxies();

    if ($universe->id) {
      $this->sendCreatedResponse($universe->toArray());
    } else {
      $this->sendInternalServerError("Erreur à la création de l'univers");
    }
  }
}
