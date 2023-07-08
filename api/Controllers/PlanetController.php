<?php

namespace App\Controllers;

use App\Dao\PlanetDao;
use App\Models\PlanetModel;
use App\Models\StructureModel;
use App\Models\ResearchModel;
use App\Models\UnitModel;
use App\Models\FightModel;
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
   * Récupération de toutes les infrastructures d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  public function getAllInfraPlanet(int $planetId): PlanetModel {
    $planet = $this->getPlanet($planetId);

    // Récupération des structures
    $structureController = new StructureController($planet->id);
    $planet->structures = $structureController->getItemsPlanet();

    // Récupération des recherches
    $researchController = new ResearchController($planet->id);
    $planet->researches = $researchController->getItemsPlanet();

    // Récupération des ressources
    $resourceController = new ResourceController($planet->id);
    $planet->resources = $resourceController->getResourcesPlanet();

    // Récupération des unités
    $unitController = new UnitController($planet->id);
    $planet->units = $unitController->getUnitsPlanet();

    return $planet;
  }


  /**
   * Récupération des ressources d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return ResourceModel[] Ressources de la planète
   */
  public function getResources(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Récupération des ressources
    $resourceController = new ResourceController($planet->id);
    return $resourceController->getResourcesPlanet();
  }


  /**
   * Récupération des structures d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return StructureModel[] Structures de la planète
   */
  public function getStructures(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Récupération des structures
    $structureController = new StructureController($planet->id);
    return $structureController->getItemsPlanet();
  }


  /**
   * Récupération des recherches d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return ResearchModel[] Recherches de la planète
   */
  public function getResearches(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Récupération des recherches
    $researchController = new ResearchController($planet->id);
    return $researchController->getItemsPlanet();
  }


  /**
   * Récupération des unités d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return UnitModel[] Unités de la planète
   */
  public function getUnits(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Récupération des recherches
    $unitController = new UnitController($planet->id);
    return $unitController->getUnitsPlanet();
  }


  /**
   * Récupération des unités d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return UnitModel[] Unités de la planète
   */
  public function getUnitTypes(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Récupération des recherches
    $unitController = new UnitController($planet->id);
    return $unitController->getItems();
  }


  /**
   * Récupération des combats d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return FightModel[] Liste des combats de la planète
   */
  public function getFights(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Récupération des combats
    $fightController = new FightController();
    return $fightController->getFightsPlanet($planet->id);
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
    $planet = $this->getPlanet($planetId);

    // Vérification de l'existence de l'utilisateur
    $userController = new UserController();
    $user = $userController->getUser($userId);

    $isUpdatePlanet = $this->planetDao->updateOne($planet->id, $user->id);

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
   * @return FightModel Données du combat
   */
  public function createFight(int $attackPlanetId, int $defensePlanetId, array $attackUnitIds): FightModel {
    // Récupération des données de la planète attaquante
    $attackPlanet = $this->getAllInfraPlanet($attackPlanetId);

    // Récupération des données de la planète attaquée
    $defensePlanet = $this->getAllInfraPlanet($defensePlanetId);

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

    return $fightController->getFight($fightId);
  }


  /**
   * Mise à jour des ressources d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return ResourceModel[] Ressources de la planète
   */
  public function updateResources(int $planetId): array {
    $planet = $this->getPlanet($planetId);

    // Mise à jour de la quantité des ressources
    $resourceController = new ResourceController($planet->id);
    $planet->resources = $resourceController->getResourcesPlanet();
    $resourceController->updateResourcesPlanet($planet->resources);

    return $planet->resources;
  }


  /**
   * Upgrade d'une structure d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de la structure
   * @param string $action Action sur la structure (start ou finish)
   * @return StructureModel Données de la structure
   */
  public function upgradeStructure(int $planetId, string $itemId, string $action): StructureModel {
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
   * @return ResearchModel Données de la recherche
   */
  public function upgradeResearch(int $planetId, string $itemId, string $action): ResearchModel {
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
   * @return UnitModel Données de l'unité
   */
  public function startCreateUnit(int $planetId, string $itemId): UnitModel {
    $planet = $this->getPlanet($planetId);

    // Récupération de l'item
    $unitController = new UnitController($planet->id);
    $unit = $unitController->getItem($itemId);

    // Récupération des ressources
    $resourceController = new ResourceController($planet->id);
    $planet->resources = $resourceController->getResourcesPlanet();

    // Contrôle de la disponibilité des ressources pour créer l'unité
    if (!$resourceController->checkAvailabilityResources($planet->resources, $unit)) {
      throw new Exceptions\InternalErrorException("Ressources insuffisantes");
    }

    // Mise à jour des ressources sur la planète
    $resourceController->updateResourcesPlanet($planet->resources);

    // Création de l'unité
    return $unitController->startCreateUnitPlanet($unit);
  }


  /**
   * Finalisation de la création d'une unité sur une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @param integer $unitId Identifiant de l'unité
   * @return UnitModel Données de l'unité
   */
  public function finishCreateUnit(int $planetId, int $unitId): UnitModel {
    $planet = $this->getPlanet($planetId);

    // Récupération de l'unité
    $unitController = new UnitController($planet->id);
    $unit = $unitController->getUnitPlanet($unitId);

    // Finalisation de la création de l'unité
    return $unitController->finishCreateUnitPlanet($unit);
  }


  /**
   * Démarrage de l'upgrade d'un item d'une planète
   *
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de l'item
   * @return StructureModel|ResearchModel Données de l'item
   */
  private function startUpgradeItem(StructureController|ResearchController $itemController, int $planetId, string $itemId): StructureModel|ResearchModel {
    $planet = $this->getAllInfraPlanet($planetId);

    // Récupération de l'item
    $item = $itemController->getItemPlanet($itemId);

    // Récupération des ressources
    $resourceController = new ResourceController($planet->id);

    // Contrôle de la disponibilité des ressources pour upgrade
    if (!$resourceController->checkAvailabilityResources($planet->resources, $item)) {
      throw new Exceptions\InternalErrorException("Ressources insuffisantes");
    }

    // Mise à jour des ressources sur la planète
    $resourceController->updateResourcesPlanet($planet->resources);

    // Mise à jour de l'item
    $itemController->startUpgradeItem($item);

    // Récupération des données mises à jour sur l'item
    return $itemController->getItemPlanet($itemId);
  }


  /**
   * Finalisation de l'upgrade d'un item d'une planète
   *
   * @param StructureController|ResearchController $itemController Contrôleur des items
   * @param integer $planetId Identifiant de la planète
   * @param string $itemId Identifiant de l'item
   * @return StructureModel|ResearchModel Données de l'item
   */
  private function finishUpgradeItem(StructureController|ResearchController $itemController, int $planetId, string $itemId): StructureModel|ResearchModel {
    $planet = $this->getAllInfraPlanet($planetId);

    // Récupération de l'item
    $item = $itemController->getItemPlanet($itemId);

    // Mise à jour des ressources sur la planète avant upgrade
    $resourceController = new ResourceController($planet->id);

    // Mise à jour de l'item
    $itemController->finishUpgradeItem($item);

    // Récupération des données mises à jour sur l'item
    $item = $itemController->getItemPlanet($itemId);

    // Mise à jour des ressources si l'item est lié à la ressource "Energie"
    $resourceController->refreshResources($planet->resources, $itemId);
    $resourceController->updateResourcesPlanet($planet->resources);

    return $item;
  }
}
