<?php

namespace App\Routes;

use App\Controllers\PlanetController;
use App\Models\PlanetModel;
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
      // Mise à jour des ressources sur une planète
      if ($this->requestMethod === "PUT") {
        $this->requestUpdateResources();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/structures/:structure_id
    elseif (preg_match("/\/api\/planets\/\d+\/structures\/[A-Z_]+$/", $uri)) {
      // Upgrade d'une structure
      if ($this->requestMethod === "PUT") {
        $this->requestUpgradeStructure();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/researches/:research_id
    elseif (preg_match("/\/api\/planets\/\d+\/researches\/[A-Z_]+$/", $uri)) {
      // Upgrade d'une recherche
      if ($this->requestMethod === "PUT") {
        $this->requestUpgradeResearch();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/planets/:planet_id/units
    elseif (preg_match("/\/api\/planets\/\d+\/units$/", $uri)) {
      // Début de la création d'une unité
      if ($this->requestMethod === "POST") {
        $this->requestStartCreateUnit();
      } else {
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
      // Récupération des combats
      if ($this->requestMethod === "GET") {
        $this->requestGetFights();
      }
      // Ajout d'un nouveau combat
      elseif ($this->requestMethod === "POST") {
        $this->requestCreateFight();
      } else {
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
   * Requête récupération des combats sur une planète
   */
  private function requestGetFights(): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $planet = $this->planetController->getFights($planetId);
    $this->sendSuccessResponse($planet->toArray());
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
    $ownerId = $this->planetController->getOwnerPlanet($planetId);

    // Vérification du propriétaire de la planète
    if ($ownerId !== 0) {
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
    $ownerId = $this->planetController->getOwnerPlanet($attackPlanetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($ownerId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $defensePlanetId = (int) $this->body["defense_planet"];
    $attackUnitIds = $this->body["attack_units"];

    $attackPlanet = $this->planetController->createFight($attackPlanetId, $defensePlanetId, $attackUnitIds);
    $this->sendSuccessResponse($attackPlanet->toArray());
  }


  /**
   * Requête mise à jour des ressources sur une planète
   */
  private function requestUpdateResources(): void {
    $planetId = (int) ($this->params[0]) ?? 0;

    // Récupération du propriétaire de la planète
    $ownerId = $this->planetController->getOwnerPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($ownerId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $planet = $this->planetController->updateResources($planetId);
    $this->sendSuccessResponse($planet->toArray());
  }


  /**
   * Requête upgrade d'une structure sur une planète
   */
  private function requestUpgradeStructure(): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $itemId = $this->params[1] ?? "";

    // Contenu de la requête non valide
    if (!$this->checkBodyUpgradeItem()) {
      throw new Exceptions\UnprocessableException();
    }

    // Récupération du propriétaire de la planète
    $ownerId = $this->planetController->getOwnerPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($ownerId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $action = $this->body["upgrade"];

    $planet = $this->planetController->upgradeStructure($planetId, $itemId, $action);

    $this->sendSuccessResponse($planet->toArray());
  }


  /**
   * Requête upgrade d'une recherche sur une planète
   */
  private function requestUpgradeResearch(): void {
    $planetId = (int) ($this->params[0]) ?? 0;
    $itemId = $this->params[1] ?? "";

    // Contenu de la requête non valide
    if (!$this->checkBodyUpgradeItem()) {
      throw new Exceptions\UnprocessableException();
    }

    // Récupération du propriétaire de la planète
    $ownerId = $this->planetController->getOwnerPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($ownerId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $action = $this->body["upgrade"];

    $planet = $this->planetController->upgradeResearch($planetId, $itemId, $action);

    $this->sendSuccessResponse($planet->toArray());
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
    $ownerId = $this->planetController->getOwnerPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($ownerId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $itemId = $this->body["item_id"];

    $planet = $this->planetController->startCreateUnit($planetId, $itemId);

    $this->sendCreatedResponse($planet->toArray());
  }


  /**
   * Requête finalisation de création d'une unité sur une planète
   */
  private function requestFinishCreateUnit(): void {
    $planetId = (int) ($this->params[0] ?? 0);
    $unitId = (int) ($this->params[1] ?? 0);

    // Récupération du propriétaire de la planète
    $ownerId = $this->planetController->getOwnerPlanet($planetId);

    // Contrôle d'autorisation
    if (!$this->checkAuth($ownerId)) {
      throw new Exceptions\UnauthorizedException();
    }

    $planet = $this->planetController->finishCreateUnit($planetId, $unitId);

    $this->sendSuccessResponse($planet->toArray());
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
