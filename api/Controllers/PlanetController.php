<?php

namespace App\Controllers;

use App\Dao\PlanetDao;
use App\Models\PlanetModel;
use App\Exceptions;

class PlanetController extends BaseController {
  private $planetDao = null;
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
    // Endpoint /api/planets/:planet_id
    if (preg_match("/\/api\/planets\/\d+$/", $uri)) {
      switch ($this->requestMethod) {
          // Récupération d'une planète
        case "GET":
          $planet = $this->getPlanet();
          $this->sendSuccessResponse($planet->toArray());
          break;

          // Assignation d'un utilisateur à une planète
        case "PUT":
          $this->assignUserPlanet();
          $this->sendSuccessResponse();

          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets
    elseif (preg_match("/\/api\/planets$/", $uri)) {
      // Récupération des planètes
      if ($this->requestMethod === "GET") {
        $planets = $this->getPlanets();

        $arrayPlanets = [
          "planets" => array_map(function (PlanetModel $planet) {
            return $planet->toArray();
          }, $planets)
        ];

        $this->sendSuccessResponse($arrayPlanets);
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/ressources
    elseif (preg_match("/\/api\/planets\/\d+\/resources$/", $uri)) {
      // Mise à jour des ressources sur une planète
      if ($this->requestMethod === "PUT") {
        $planet = $this->updateResourcesPlanet();
        $this->sendSuccessResponse($planet->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/structures/:structure_id
    elseif (preg_match("/\/api\/planets\/\d+\/structures\/[A-Z_]+$/", $uri)) {
      // Upgrade d'une structure
      if ($this->requestMethod === "PUT") {
        $planet = $this->upgradeStructurePlanet();
        $this->sendSuccessResponse($planet->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/researches/:research_id
    elseif (preg_match("/\/api\/planets\/\d+\/researches\/[A-Z_]+$/", $uri)) {
      // Upgrade d'une recherche
      if ($this->requestMethod === "PUT") {
        $planet = $this->upgradeResearchPlanet();
        $this->sendSuccessResponse($planet->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/units
    elseif (preg_match("/\/api\/planets\/\d+\/units$/", $uri)) {
      // Début de la création d'une unité
      if ($this->requestMethod === "POST") {
        $planet = $this->startCreateUnitPlanet();
        $this->sendCreatedResponse($planet->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/units/:unit_id
    elseif (preg_match("/\/api\/planets\/\d+\/units\/\d+$/", $uri)) {
      // Finalisation de la création d'une unité
      if ($this->requestMethod === "PUT") {
        $planet = $this->finishCreateUnitPlanet();
        $this->sendSuccessResponse($planet->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    } else {
      throw new Exceptions\NotFoundException("URL non valide");
    }
  }


  /**
   * Récupération d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  public function getPlanet(int $planetId = 0): PlanetModel {
    $planetId = (int) ($this->params[0] ?? $planetId);

    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\NotFoundException("Planète non trouvée");
    }

    // Récupération des structures
    $structureController = new StructureController($planetId);
    $planet->structures = $structureController->getItemsPlanet();

    // Récupération des recherches
    $researchController = new ResearchController($planetId);
    $planet->researches = $researchController->getItemsPlanet();

    // Récupération des ressources
    $resourceController = new ResourceController($planetId);
    $planet->resources = $resourceController->getResourcesPlanet();

    // Récupération des unités
    $unitController = new UnitController($planetId);
    $planet->units = $unitController->getUnitsPlanet();

    return $planet;
  }


  /**
   * Récupération des planètes
   *
   * @return PlanetModel[] Liste des planètes
   */
  public function getPlanets(): array {
    return $this->planetDao->findAll();
  }


  /**
   * Récupération des planètes d'un système solaire
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @return PlanetModel[] Liste des planètes
   */
  public function getPlanetsBySolarSystem(int $solarSystemId): array {
    return $this->planetDao->findAllBySolarSystem($solarSystemId);
  }


  /**
   * Création de planètes pour plusieurs systèmes solaires
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @return PlanetModel[] Liste des planètes
   */
  public function createPlanets(int $solarSystemId = 0): array {
    $solarSystemId = (int) ($this->params[0] ?? $solarSystemId);

    // Génération du nom des planètes
    $names = $this->randomName(rand(4, 10));

    return $this->planetDao->insertMultiples($solarSystemId, $names);
  }


  /**
   * Assignation d'un utilisateur à une planète
   */
  private function assignUserPlanet(): void {
    // Données de l'utilisateur non valide
    if (!$this->checkBodyUserId()) {
      throw new Exceptions\UnprocessableException();
    }

    $planetId = (int) ($this->params[0] ?? 0);
    $userId = (int) $this->body["user_id"];

    // Vérification de l'existence de la planète
    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\NotFoundException("Planète non trouvée");
    }

    // Vérification de l'existence de l'utilisateur
    $userController = new UserController();
    $user = $userController->getUser($userId);

    $isUpdatePlanet = $this->planetDao->updateOne($planetId, $user->id);

    if (!$isUpdatePlanet) {
      throw new Exceptions\InternalErrorException("Assignation de l'utilisateur a échoué");
    }
  }


  /**
   * Mise à jour des ressources d'une planète
   *
   * @return PlanetModel Données de la planète
   */
  private function updateResourcesPlanet(): PlanetModel {
    $planetId = (int) ($this->params[0] ?? 0);

    // Vérification de l'existence de la planète
    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\InternalErrorException("Planète non trouvée");
    }

    // Mise à jour de la quantité des ressources
    $resourceController = new ResourceController($planetId);
    $planet->resources = $resourceController->getResourcesPlanet();
    $resourceController->updateResourcesPlanet();

    return $planet;
  }


  /**
   * Upgrade d'une structure d'une planète
   *
   * @return PlanetModel Données de la planète
   */
  private function upgradeStructurePlanet(): PlanetModel {
    // Contenu de la requête non valide
    if (!$this->checkBodyUpgradeItem()) {
      throw new Exceptions\UnprocessableException();
    }

    $planetId = (int) ($this->params[0] ?? 0);
    $action = $this->body["upgrade"];

    $structureController = new StructureController($planetId);

    if ($action === "start") {
      // Déclenchement de l'upgrade
      return $this->startUpgradeItemPlanet($structureController, $planetId);
    } else {
      // Finalisation de l'upgrade
      return $this->finishUpgradeItemPlanet($structureController, $planetId);
    }
  }


  /**
   * Upgrade d'une recherche d'une planète
   *
   * @return PlanetModel Données de la planète
   */
  private function upgradeResearchPlanet(): PlanetModel {
    // Contenu de la requête non valide
    if (!$this->checkBodyUpgradeItem()) {
      throw new Exceptions\UnprocessableException();
    }

    $planetId = (int) ($this->params[0] ?? 0);
    $action = $this->body["upgrade"];

    $researchController = new ResearchController($planetId);

    if ($action === "start") {
      // Déclenchement de l'upgrade
      return $this->startUpgradeItemPlanet($researchController, $planetId);
    } else {
      // Finalisation de l'upgrade
      return $this->finishUpgradeItemPlanet($researchController, $planetId);
    }
  }


  /**
   * Démarrage de l'upgrade d'un item d'une planète
   *
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  private function startUpgradeItemPlanet(StructureController|ResearchController $itemController, int $planetId): PlanetModel {
    $itemId = $this->params[1] ?? "";

    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\InternalErrorException("Planète non trouvée");
    }

    // Récupération de l'item
    $item = $itemController->getItemPlanet($itemId);

    // Récupération des ressources
    $resourceController = new ResourceController($planetId);
    $planet->resources = $resourceController->getResourcesPlanet();

    // Contrôle de la disponibilité des ressources pour upgrade
    if (!$resourceController->checkAvailabilityResources($item)) {
      throw new Exceptions\InternalErrorException("Ressources insuffisantes");
    }

    // Mise à jour des ressources sur la planète
    $resourceController->updateResourcesPlanet();

    // Mise à jour de l'item
    $itemController->startUpgradeItem($item);

    // Récupération des données mises à jour sur l'item
    $item = $itemController->getItemPlanet($itemId);

    switch ($item->type) {
      case "STRUCTURE":
        $planet->structures = [$item];
        break;
      case "RESEARCH":
        $planet->researches = [$item];
        break;
      default:
        break;
    }

    $planet->resources = $resourceController->refreshResources();

    return $planet;
  }


  /**
   * Finalisation de l'upgrade d'un item d'une planète
   *
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  private function finishUpgradeItemPlanet(StructureController|ResearchController $itemController, int $planetId): PlanetModel {
    $itemId = $this->params[1] ?? "";

    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\InternalErrorException("Planète non trouvée");
    }

    // Récupération de l'item
    $item = $itemController->getItemPlanet($itemId);

    // Mise à jour des ressources sur la planète avant upgrade
    $resourceController = new ResourceController($planetId);
    $planet->resources = $resourceController->getResourcesPlanet();

    // Mise à jour de l'item
    $itemController->finishUpgradeItem($item);

    // Récupération des données mises à jour sur l'item
    $item = $itemController->getItemPlanet($itemId);

    switch ($item->type) {
      case "STRUCTURE":
        $planet->structures = [$item];
        break;
      case "RESEARCH":
        $planet->researches = [$item];
        break;
      default:
        break;
    }

    // Mise à jour des ressources si l'item est lié à la ressource "Energie"
    $resourceController->refreshResources($itemId);
    $resourceController->updateResourcesPlanet();

    $planet->resources = $resourceController->refreshResources();

    return $planet;
  }


  /**
   * Création d'une unité sur une planète
   *
   * @return PlanetModel Données de la planète
   */
  private function startCreateUnitPlanet(): PlanetModel {
    // Contenu de la requête non valide
    if (!$this->checkBodyCreateUnit()) {
      throw new Exceptions\UnprocessableException();
    }

    $planetId = (int) ($this->params[0] ?? 0);
    $itemId = $this->body["item_id"];

    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\InternalErrorException("Planète non trouvée");
    }

    // Récupération de l'item
    $unitController = new UnitController($planetId);
    $unit = $unitController->getItem($itemId);

    // Récupération des ressources
    $resourceController = new ResourceController($planetId);
    $planet->resources = $resourceController->getResourcesPlanet();

    // Contrôle de la disponibilité des ressources pour créer l'unité
    if (!$resourceController->checkAvailabilityResources($unit)) {
      throw new Exceptions\InternalErrorException("Ressources insuffisantes");
    }

    // Mise à jour des ressources sur la planète
    $resourceController->updateResourcesPlanet();

    // Création de l'unité
    $unit = $unitController->startCreateUnitPlanet($unit);

    $planet->units = [$unit];
    $planet->resources = $resourceController->refreshResources();

    return $planet;
  }


  /**
   * Finalisation de la création d'une unité sur une planète
   *
   * @return PlanetModel Données de la planète
   */
  private function finishCreateUnitPlanet(): PlanetModel {
    $planetId = (int) ($this->params[0] ?? 0);
    $unitId = (int) ($this->params[1] ?? 0);

    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\InternalErrorException("Planète non trouvée");
    }

    // Récupération de l'unité
    $unitController = new UnitController($planetId);
    $unit = $unitController->getUnitPlanet($unitId);

    // Finalisation de la création de l'unité
    $unit = $unitController->finishCreateUnitPlanet($unit);

    $planet->units = [$unit];

    return $planet;
  }


  /**
   * Contrôle du contenu de la requête lors de l'assignation d'un utilisateur à une planète
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyUserId(): bool {
    $userId = $this->body["user_id"] ?? "";

    return (bool) $userId;
  }


  /**
   * Contrôle du contenu de la requête lors de l'upgrade d'un item
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyUpgradeItem(): bool {
    $upgrade = $this->body["upgrade"] ?? "";

    return $upgrade === "start" || $upgrade === "finish";
  }


  /**
   * Contrôle du contenu de la requête lors de la création d'une unité
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyCreateUnit(): bool {
    $itemId = $this->body["item_id"] ?? "";

    return (bool) $itemId;
  }
}
