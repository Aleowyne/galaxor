<?php

namespace App\Controllers;

use App\Dao\PlanetDao;
use App\Models\PlanetModel;
use App\Exceptions;

class PlanetController extends BaseController {
  private PlanetDao $planetDao;

  /**
   * Constructeur
   */
  public function __construct() {
    $this->planetDao = new PlanetDao();
  }


  /**
   * Récupération d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  public function getPlanet(int $planetId): PlanetModel {
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
   * Récupération du propriétaire d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return integer Identifiant du propriétaire
   */
  public function getOwnerPlanet(int $planetId): int {
    $planet = $this->planetDao->findOne($planetId);
    return $planet->userId;
  }


  /**
   * Récupération des combats d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  public function getFights(int $planetId): PlanetModel {
    $planet = $this->planetDao->findOne($planetId);

    if (!$planet->id) {
      throw new Exceptions\NotFoundException("Planète non trouvée");
    }

    // Récupération des combats
    $fightController = new FightController();
    $planet->fights = $fightController->getFightsPlanet($planet->id);

    return $planet;
  }


  /**
   * Création de planètes pour plusieurs systèmes solaires
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @return PlanetModel[] Liste des planètes
   */
  public function createPlanets(int $solarSystemId): array {
    // Génération du nom des planètes
    $names = $this->randomName(rand(4, 10));

    return $this->planetDao->insertMultiplesBySolarSystem($solarSystemId, $names);
  }


  /**
   * Assignation d'un utilisateur à une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @param integer $userId Identifiant de l'utilisateur
   */
  public function assignUser(int $planetId, int $userId): void {
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
   * Lancement d'un combat
   *
   * @param integer $attackPlanetId Identifiant de la planète attaquante
   * @param integer $defensePlanetId Identifiant de la planète attaquée
   * @param integer[] $attackUnitIds Identifiants des unités de la planète attaquante
   * @return PlanetModel Données de la planète
   */
  public function createFight(int $attackPlanetId, int $defensePlanetId, array $attackUnitIds): PlanetModel {
    // Récupération des données de la planète attaquante
    $attackPlanet = $this->getPlanet($attackPlanetId);

    // Récupération des données de la planète attaquée
    $defensePlanet = $this->getPlanet($defensePlanetId);

    // Si la planète n'appartient à personne
    if ($defensePlanet->userId === 0) {
      throw new Exceptions\InternalErrorException("Impossible d'attaquer une planète sans propriétaire");
    }

    // Si les 2 planètes appartiennent au même propriétaire
    if ($defensePlanet->userId === $attackPlanet->userId) {
      throw new Exceptions\InternalErrorException("Impossible d'attaquer une planète dont vous êtes le propriétaire");
    }

    // Lancement du combat
    $fightController = new FightController($attackPlanet, $defensePlanet);
    $fightId = $fightController->createFightPlanet($attackUnitIds);

    $attackPlanet = $this->getPlanet($attackPlanetId);
    $attackPlanet->fights[] = $fightController->getFight($fightId);

    return $attackPlanet;
  }


  /**
   * Mise à jour des ressources d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  public function updateResources(int $planetId): PlanetModel {
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
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de la structure
   * @param string $action Action sur la structure (start ou finish)
   * @return PlanetModel Données de la planète
   */
  public function upgradeStructure(int $planetId, string $itemId, string $action): PlanetModel {
    $structureController = new StructureController($planetId);

    if ($action === "start") {
      // Déclenchement de l'upgrade de la structure
      return $this->startUpgradeItem($structureController, $planetId, $itemId);
    } else {
      // Finalisation de l'upgrade de la structure
      return $this->finishUpgradeItem($structureController, $planetId, $itemId);
    }
  }


  /**
   * Upgrade d'une recherche d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de la recherche
   * @param string $action Action sur la recherche (start ou finish)
   * @return PlanetModel Données de la planète
   */
  public function upgradeResearch(int $planetId, string $itemId, string $action): PlanetModel {
    $researchController = new ResearchController($planetId);

    if ($action === "start") {
      // Déclenchement de l'upgrade de la recherche
      return $this->startUpgradeItem($researchController, $planetId, $itemId);
    } else {
      // Finalisation de l'upgrade de la recherche
      return $this->finishUpgradeItem($researchController, $planetId, $itemId);
    }
  }


  /**
   * Création d'une unité sur une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de l'item
   * @return PlanetModel Données de la planète
   */
  public function startCreateUnit(int $planetId, string $itemId): PlanetModel {
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
   * @param integer $planetId Identifiant de la planète
   * @param integer $unitId Identifiant de l'unité
   * @return PlanetModel Données de la planète
   */
  public function finishCreateUnit(int $planetId, int $unitId): PlanetModel {
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
   * Démarrage de l'upgrade d'un item d'une planète
   *
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de l'item
   * @return PlanetModel Données de la planète
   */
  private function startUpgradeItem(StructureController|ResearchController $itemController, int $planetId, string $itemId): PlanetModel {
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
   * @param string $itemId Identifiant de l'item
   * @return PlanetModel Données de la planète
   */
  private function finishUpgradeItem(StructureController|ResearchController $itemController, int $planetId, string $itemId): PlanetModel {
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
}
