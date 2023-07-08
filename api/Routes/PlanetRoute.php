<?php

namespace App\Routes;

use App\Controllers\PlanetController;
use App\Models\PlanetModel;
use App\Models\ResourceModel;
use App\Models\StructureModel;
use App\Models\ResearchModel;
use App\Models\UnitModel;
use App\Models\FightModel;
use App\Exceptions;

class PlanetRoute extends BaseRoute {
  private PlanetController $planetController;
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
    $this->planetController = new PlanetController();
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
          $this->requestGetPlanet();
          break;

          // Assignation d'un utilisateur à une planète
        case "PUT":
          $this->requestAssignUser();
          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets
    elseif (preg_match("/\/api\/planets$/", $uri)) {
      // Récupération des planètes
      if ($this->requestMethod === "GET") {
        $this->requestGetPlanets();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/ressources
    elseif (preg_match("/\/api\/planets\/\d+\/resources$/", $uri)) {
      switch ($this->requestMethod) {
          // Récupération des ressources sur une planète
        case "GET":
          $this->requestGetResources();
          break;

          // Mise à jour des ressources sur une planète
        case "PUT":
          $this->requestUpdateResources();
          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/structures
    elseif (preg_match("/\/api\/planets\/\d+\/structures$/", $uri)) {
      // Récupération des structures d'une planète
      if ($this->requestMethod === "GET") {
        $this->requestGetStructures();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/structures/:structure_id/start
    elseif (preg_match("/\/api\/planets\/\d+\/structures\/[A-Z_]+\/start$/", $uri)) {
      // Lancement de l'upgrade d'une structure
      if ($this->requestMethod === "PUT") {
        $this->requestUpgradeStructure("start");
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/structures/:structure_id/finish
    elseif (preg_match("/\/api\/planets\/\d+\/structures\/[A-Z_]+\/finish$/", $uri)) {
      // Finalisation de l'upgrade d'une structure
      if ($this->requestMethod === "PUT") {
        $this->requestUpgradeStructure("finish");
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/researches
    elseif (preg_match("/\/api\/planets\/\d+\/researches$/", $uri)) {
      // Récupération des recherches d'une planète
      if ($this->requestMethod === "GET") {
        $this->requestGetResearches();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/researches/:research_id/start
    elseif (preg_match("/\/api\/planets\/\d+\/researches\/[A-Z_]+\/start$/", $uri)) {
      // Lancement de l'upgrade d'une recherche
      if ($this->requestMethod === "PUT") {
        $this->requestUpgradeResearch("start");
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/researches/:research_id/finish
    elseif (preg_match("/\/api\/planets\/\d+\/researches\/[A-Z_]+\/finish$/", $uri)) {
      // Finalisation de l'upgrade d'une recherche
      if ($this->requestMethod === "PUT") {
        $this->requestUpgradeResearch("finish");
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/unittypes
    elseif (preg_match("/\/api\/planets\/\d+\/unittypes$/", $uri)) {
      // Récupération des types d'unités
      if ($this->requestMethod === "GET") {
        $this->requestGetUnitTypes();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/units
    elseif (preg_match("/\/api\/planets\/\d+\/units$/", $uri)) {
      switch ($this->requestMethod) {
          // Récupération des unités sur une planète
        case "GET":
          $this->requestGetUnits();
          break;

          // Début de la création d'une unité
        case "POST":
          $this->requestStartCreateUnit();
          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/units/:unit_id
    elseif (preg_match("/\/api\/planets\/\d+\/units\/\d+$/", $uri)) {
      // Finalisation de la création d'une unité
      if ($this->requestMethod === "PUT") {
        $this->requestFinishCreateUnit();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/fights
    elseif (preg_match("/\/api\/planets\/\d+\/fights$/", $uri)) {
      switch ($this->requestMethod) {
          // Récupération des combats
        case "GET":
          $this->requestGetFights();
          break;

          // Ajout d'un nouveau combat
        case "POST":
          $this->requestCreateFight();
          break;

        default:
          throw new Exceptions\MethodNotAllowedException();
      }
    } else {
      throw new Exceptions\NotFoundException("URL non valide");
    }
  }


  /**
   * Requête récupération d'une planète
   */
  private function requestGetPlanet(): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $planet = $this->planetController->getPlanet($planetId);
    $this->sendSuccessResponse($planet->toArray());
  }


  /**
   * Requête récupération d'une liste de planète
   */
  private function requestGetPlanets(): void {
    $planets = $this->planetController->getPlanets();

    $arrayPlanets = [
      "planets" => array_map(function (PlanetModel $planet) {
        return $planet->toArray();
      }, $planets)
    ];

    $this->sendSuccessResponse($arrayPlanets);
  }


  /**
   * Requête récupération des ressources d'une planète
   */
  private function requestGetResources(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    $resources = $this->planetController->getResources($planetId);

    $arrayResources = [
      "resources" => array_map(function (ResourceModel $resource) {
        return $resource->toArray();
      }, $resources)
    ];

    $this->sendSuccessResponse($arrayResources);
  }


  /**
   * Requête récupération des structures d'une planète
   */
  private function requestGetStructures(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    $structures = $this->planetController->getStructures($planetId);

    $arrayStructures = [
      "structures" => array_map(function (StructureModel $structure) {
        return $structure->toArray();
      }, $structures)
    ];

    $this->sendSuccessResponse($arrayStructures);
  }


  /**
   * Requête récupération des recherches d'une planète
   */
  private function requestGetResearches(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    $researches = $this->planetController->getResearches($planetId);

    $arrayResearches = [
      "reasearches" => array_map(function (ResearchModel $research) {
        return $research->toArray();
      }, $researches)
    ];

    $this->sendSuccessResponse($arrayResearches);
  }


  /**
   * Requête récupération des unités d'une planète
   */
  private function requestGetUnits(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    $units = $this->planetController->getUnits($planetId);

    $arrayUnits = [
      "units" => array_map(function (UnitModel $unit) {
        return $unit->toArray();
      }, $units)
    ];

    $this->sendSuccessResponse($arrayUnits);
  }


  /**
   * Requête récupération des types d'unités d'une planète
   */
  private function requestGetUnitTypes(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    $unitTypes = $this->planetController->getUnitTypes($planetId);

    $arrayUnitTypes = [
      "unit_types" => array_map(function (UnitModel $unitType) {
        return $unitType->toArrayForType();
      }, $unitTypes)
    ];

    $this->sendSuccessResponse($arrayUnitTypes);
  }


  /**
   * Requête récupération des combats sur une planète
   */
  private function requestGetFights(): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $fights = $this->planetController->getFights($planetId);

    $arrayFights = [
      "fights" => array_map(function (FightModel $fight) {
        return $fight->toArray();
      }, $fights)
    ];

    $this->sendSuccessResponse($arrayFights);
  }


  /**
   * Requête assignation d'un utilisateur à une planète
   */
  private function requestAssignUser(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    // Contenu de la requête non valide
    if (!$this->checkBodyUserId()) {
      throw new Exceptions\UnprocessableException();
    }

    $userId = (int) $this->body["user_id"];

    // Contrôle d'autorisation
    if (!$this->checkAuth($userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($planetId);

    // Vérification du propriétaire de la planète
    if ($planet->userId !== 0) {
      throw new Exceptions\NotFoundException("Planète déjà habitée");
    }

    $this->planetController->assignUser($planetId, $userId);
    $this->sendNoContentResponse();
  }


  /**
   * Requête création d'un combat entre 2 planètes
   */
  private function requestCreateFight(): void {
    $attackPlanetId = (int) ($this->params[0]) ?? 0;

    // Contenu de la requête non valide
    if (!$this->checkBodyCreateFight()) {
      throw new Exceptions\UnprocessableException();
    }

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($attackPlanetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($planet->userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $defensePlanetId = (int) $this->body["defense_planet"];
    $attackUnitIds = $this->body["attack_units"];

    $fight = $this->planetController->createFight($attackPlanetId, $defensePlanetId, $attackUnitIds);
    $this->sendSuccessResponse($fight->toArray());
  }


  /**
   * Requête mise à jour des ressources sur une planète
   */
  private function requestUpdateResources(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($planet->userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $resources = $this->planetController->updateResources($planetId);

    $arrayResources = [
      "resources" => array_map(function (ResourceModel $resource) {
        return $resource->toArray();
      }, $resources)
    ];

    $this->sendSuccessResponse($arrayResources);
  }


  /**
   * Requête upgrade d'une structure sur une planète
   *
   * @param string $action Action sur la structure (start ou finish)
   */
  private function requestUpgradeStructure(string $action): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $itemId = $this->params[1] ?? "";

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($planet->userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $structure = $this->planetController->upgradeStructure($planetId, $itemId, $action);

    $this->sendSuccessResponse($structure->toArray());
  }


  /**
   * Requête upgrade d'une recherche sur une planète
   *
   * @param string $action Action sur la recherche (start ou finish)
   */
  private function requestUpgradeResearch(string $action): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $itemId = $this->params[1] ?? "";

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($planet->userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $research = $this->planetController->upgradeResearch($planetId, $itemId, $action);

    $this->sendSuccessResponse($research->toArray());
  }


  /**
   * Requête début de création d'une unité sur une planète
   */
  private function requestStartCreateUnit(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    // Contenu de la requête non valide
    if (!$this->checkBodyCreateUnit()) {
      throw new Exceptions\UnprocessableException();
    }

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($planet->userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $itemId = $this->body["item_id"];

    $unit = $this->planetController->startCreateUnit($planetId, $itemId);

    $this->sendCreatedResponse($unit->toArray());
  }


  /**
   * Requête finalisation de création d'une unité sur une planète
   */
  private function requestFinishCreateUnit(): void {
    $planetId = (int) ($this->params[0] ?? 0);
    $unitId = (int) ($this->params[1] ?? 0);

    // Récupération du propriétaire de la planète
    $planet = $this->planetController->getPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($planet->userId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $unit = $this->planetController->finishCreateUnit($planetId, $unitId);

    $this->sendSuccessResponse($unit->toArray());
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
   * Contrôle du contenu de la requête lors de la création d'une unité
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyCreateUnit(): bool {
    $itemId = $this->body["item_id"] ?? "";

    return (bool) $itemId;
  }


  /**
   * Contrôle du contenu de la requête lors de la création d'un combat
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyCreateFight(): bool {
    $defensePlanetId = $this->body["defense_planet"] ?? "";
    $attackUnitIds = $this->body["attack_units"] ?? "";

    return (bool) ($defensePlanetId && $attackUnitIds);
  }
}
