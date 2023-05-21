<?php

class PlanetController extends BaseController {
  private $planetDao = null;
  private $planet = null;
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
    $this->planetDao = new PlanetDao();
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
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets */
    if (preg_match("/\/api\/planets$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getPlanets();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:id/ressources */
    if (preg_match("/\/api\/planets\/[0-9]*\/resources$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $this->updateResourcesPlanet();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:planet_id/structures/:structure_id/upgrade/start */
    if (preg_match("/\/api\/planets\/[0-9]*\/structures\/[A-Z_]*\/upgrade\/start$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $planetId = (int) ($this->params[0] ?? 0);
          $structureController = new StructureController($planetId);
          $this->startUpgradeItemPlanet($structureController, $planetId);
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:planet_id/structures/:structure_id/upgrade/finish */
    if (preg_match("/\/api\/planets\/[0-9]*\/structures\/[A-Z_]*\/upgrade\/finish*$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $planetId = (int) ($this->params[0] ?? 0);
          $structureController = new StructureController($planetId);
          $this->finishUpgradeItemPlanet($structureController, $planetId);
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:planet_id/researches/:research_id/upgrade/start */
    if (preg_match("/\/api\/planets\/[0-9]*\/researches\/[A-Z_]*\/upgrade\/start*$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $planetId = (int) ($this->params[0] ?? 0);
          $researchController = new ResearchController($planetId);
          $this->startUpgradeItemPlanet($researchController, $planetId);
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:planet_id/researches/:research_id/upgrade/finish */
    if (preg_match("/\/api\/planets\/[0-9]*\/researches\/[A-Z_]*\/upgrade\/finish*$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $planetId = (int) ($this->params[0] ?? 0);
          $researchController = new ResearchController($planetId);
          $this->finishUpgradeItemPlanet($researchController, $planetId);
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    $this->sendErrorResponse("URL non valide");
  }


  /**
   * Récupération d'une planète
   */
  private function getPlanet(): void {
    $planetId = (int) ($this->params[0] ?? 0);

    $this->planet = $this->planetDao->findOne($planetId);

    if (!$this->planet->id) {
      $this->sendErrorResponse("Planète non trouvée");
      return;
    }

    // Récupération des structures
    $structureController = new StructureController($planetId);
    $this->planet->structures = $structureController->getItems();

    // Récupération des recherches
    $researchController = new ResearchController($planetId);
    $this->planet->researches = $researchController->getItems();

    // Récupération des ressources
    $resourceController = new ResourceController($planetId);
    $this->planet->resources = $resourceController->getResources();

    $this->sendSuccessResponse($this->planet->toArray());
  }


  /**
   * Récupération des planètes
   */
  private function getPlanets(): void {
    $planets = $this->planetDao->findAll();

    $arrayPlanets = [
      "planets" => array_map(function (PlanetModel $planet) {
        return $planet->toArray();
      }, $planets)
    ];

    $this->sendSuccessResponse($arrayPlanets);
  }


  /**
   * Création de planètes pour plusieurs systèmes solaires
   * 
   * @return PlanetModel[] Liste des planètes
   */
  public function createPlanets(): array {
    $solarSystemId = (int) ($this->params[0] ?? 0);

    // Génération du nom des planètes
    $names = $this->randomName(rand(4, 10));

    return $this->planetDao->insertMultiples($solarSystemId, $names);
  }


  /**
   * Assignation d'un utilisateur à une planète
   */
  private function assignUserPlanet(): void {
    // Données de l'utilisateur non valide
    if (!$this->checkUserId()) {
      $this->sendInvalidBody();
      return;
    }

    $planetId = (int) ($this->params[0] ?? 0);
    $userId = $this->body["user_id"];

    $isUpdatePlanet = $this->planetDao->updateOne($planetId, $userId);

    if ($isUpdatePlanet) {
      $this->sendSuccessResponse();
    } else {
      $this->sendErrorResponse();
    }
  }


  /**
   * Mise à jour des ressources d'une planète
   */
  private function updateResourcesPlanet(): void {
    $planetId = (int) ($this->params[0] ?? 0);

    $this->planet = $this->planetDao->findOne($planetId);

    if (!$this->planet->id) {
      $this->sendErrorResponse("Planète non trouvée");
      return;
    }

    // Mise à jour de la quantité des ressources
    $resourceController = new ResourceController($planetId);
    $this->planet->resources = $resourceController->getResources();
    $isUpdateResource = $resourceController->updateResources();

    if ($isUpdateResource) {
      $this->sendSuccessResponse($this->planet->toArray());
    } else {
      $this->sendNoContentResponse();
    }
  }


  /**
   * Démarrage de l'upgrade d'un item d'une planète
   * 
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   */
  private function startUpgradeItemPlanet(StructureController|ResearchController $itemController, int $planetId): void {
    $itemId = $this->params[1] ?? "";

    $this->planet = $this->planetDao->findOne($planetId);

    if (!$this->planet->id) {
      $this->sendErrorResponse("Planète non trouvée");
      return;
    }

    // Récupération de l'item
    $item = $itemController->getItem($itemId);

    if (!$item->id) {
      return;
    }

    // Récupération des ressources
    $resourceController = new ResourceController($planetId);
    $this->planet->resources = $resourceController->getResources();

    // Contrôle de la disponibilité des ressources pour upgrade
    if (!$resourceController->checkAvailabilityResources($item)) {
      $this->sendErrorResponse("Ressources insuffisantes");
      return;
    }

    // Mise à jour des ressources sur la planète 
    $resourceController->updateResources();

    // Mise à jour de l'item
    $isUpdateItem = $itemController->startUpgradeItem($item);

    if (!$isUpdateItem) {
      return;
    }

    $item = $itemController->getItem($itemId);

    switch ($item->type) {
      case "STRUCTURE":
        $this->planet->structures = [$item];
        break;
      case "RESEARCH":
        $this->planet->researches = [$item];
        break;
      default:
        break;
    }

    $this->planet->resources = $resourceController->refreshResources();

    $this->sendSuccessResponse($this->planet->toArray());
  }


  /**
   * Finalisation de l'upgrade d'un item d'une planète
   * 
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   */
  private function finishUpgradeItemPlanet(StructureController|ResearchController $itemController, int $planetId): void {
    $itemId = $this->params[1] ?? "";

    $this->planet = $this->planetDao->findOne($planetId);

    if (!$this->planet->id) {
      $this->sendErrorResponse("Planète non trouvée");
      return;
    }

    // Récupération de l'item
    $item = $itemController->getItem($itemId);

    if (!$item->id) {
      return;
    }

    // Mise à jour des ressources sur la planète avant upgrade
    $resourceController = new ResourceController($planetId);
    $this->planet->resources = $resourceController->getResources();

    // Mise à jour de l'item
    $isUpdateItem = $itemController->finishUpgradeItem($item);

    if (!$isUpdateItem) {
      return;
    }

    $item = $itemController->getItem($itemId);

    switch ($item->type) {
      case "STRUCTURE":
        $this->planet->structures = [$item];
        break;
      case "RESEARCH":
        $this->planet->researches = [$item];
        break;
      default:
        break;
    }

    // Mise à jour des ressources sur la planète après upgrade
    $resourceController->refreshResources($itemId);
    $resourceController->updateResources();

    $this->planet->resources = $resourceController->refreshResources();

    $this->sendSuccessResponse($this->planet->toArray());
  }

  /**
   * Contrôle des données de l'utilisateur 
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserId(): bool {
    $userId = $this->body["user_id"] ?? "";

    return (bool) $userId;
  }
}
