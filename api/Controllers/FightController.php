<?php

namespace App\Controllers;

use App\Dao\FightDao;
use App\Models\FightModel;
use App\Models\UnitModel;
use App\Models\PlanetModel;
use App\Exceptions;
use DateTime;

class FightController extends BaseController {
  private FightDao $fightDao;
  private FightModel $fight;
  private PlanetModel $attackPlanet;
  private PlanetModel $defensePlanet;
  private int $attackerAttackPoint;
  private int $attackerDefensePoint;
  private int $defenserAttackPoint;
  private int $defenserDefensePoint;

  /**
   * Constructeur
   *
   * @param PlanetModel $attackPlanet Planète attaquante
   * @param PlanetModel $defensePlanet Planète attaquée
   */
  public function __construct(PlanetModel $attackPlanet = null, PlanetModel $defensePlanet = null) {
    $this->fightDao = new FightDao();
    $this->fight = new FightModel();
    $this->attackerAttackPoint = 0;
    $this->attackerDefensePoint = 0;
    $this->defenserAttackPoint = 0;
    $this->defenserDefensePoint = 0;

    if ($attackPlanet && $defensePlanet) {
      $this->attackPlanet = $attackPlanet;
      $this->defensePlanet = $defensePlanet;
    }
  }


  /**
   * Récupération d'un combat
   *
   * @param integer $fightId Identifiant du combat
   * @return FightModel Données du combat
   */
  public function getFight(int $fightId): FightModel {
    return $this->fightDao->findOne($fightId);
  }


  /**
   * Récupération des combats d'une planète
   *
   * @param integer $planetId Identifiant de la planète
   * @return FightModel[] Liste des combats
   */
  public function getFightsPlanet(int $planetId): array {
    return $this->fightDao->findAllByPlanet($planetId);
  }



  /**
   * Lancement d'un combat entre 2 planètes
   *
   * @param integer[] $attackUnitIds Identifiants des unités attaquantes
   * @return integer Identifiant du combat
   */
  public function createFightPlanet(array $attackUnitIds): int {
    $currentDate = new DateTime();
    $this->fight->timeFight = $currentDate->format(self::FORMAT_DATE);
    $this->fight->attackPlanetId = $this->attackPlanet->id;
    $this->fight->defensePlanetId = $this->defensePlanet->id;

    // Calcul des points de défense et d'attaque des 2 planètes
    $this->calculatePoints($attackUnitIds);

    // Détermination du résultat du combat
    $this->determineFightResult();

    // Application des dommages aux structures de la planète attaquée
    if ($this->defenserDefensePoint !== 0) {
      $this->setDamagesToDefense();
    }

    // Application des dommages aux structures de la planète attaquante
    if ($this->attackerDefensePoint !== 0) {
      $disableUnits = $this->setDamagesToAttack($attackUnitIds);
    }

    // Si la planète attaquée gagne, les ressources pour construire les unités détruites de la planète attaquante
    // sont ajoutées à la planète attaquée
    if ($this->fight->result === "LOSE" && $disableUnits) {
      $this->calculateRewardsOnDefeat($disableUnits);
    }

    // Si la planète attaquante gagne
    if ($this->fight->result === "WIN") {
      $this->calculateRewardsOnVictory($attackUnitIds);
    }

    $fightId = $this->fightDao->insertOne($this->fight);

    if (!$fightId) {
      throw new Exceptions\InternalErrorException("Création du combat a échoué");
    }

    return $fightId;
  }


  /**
   * Calcul des points de défense et d'attaque des 2 planètes
   *
   * @param integer[] $attackUnitIds Identifiants des unités attaquantes
   */
  private function calculatePoints(array $attackUnitIds): void {
    // Calcul des points d'attaque et de défense de la planète attaquante
    foreach ($this->attackPlanet->units as $unit) {
      if (!$unit->createInProgress && in_array($unit->id, $attackUnitIds)) {
        $this->fight->attackUnits[] = $unit;
        $this->attackerAttackPoint += $unit->attackPoint;
        $this->attackerDefensePoint += $unit->defensePoint;
      }
    }

    // Calcul des points d'attaque et de défense de la planète attaquée, à partir des unités
    foreach ($this->defensePlanet->units as $unit) {
      if (!$unit->createInProgress) {
        $this->fight->defenseUnits[] = $unit;
        $this->defenserAttackPoint += $unit->attackPoint;
        $this->defenserDefensePoint += $unit->defensePoint;
      }
    }

    // Calcul des points d'attaque et de défense de la planète attaquée, à partir des structures
    foreach ($this->defensePlanet->structures as $structure) {
      if ($structure->level !== 0 && ($structure->attackPoint !== 0 || $structure->defensePoint !== 0)) {
        $this->fight->defenseStructures[] = $structure;
        $this->defenserAttackPoint += $structure->attackPoint;
        $this->defenserDefensePoint += $structure->defensePoint;
      }
    }
  }


  /**
   * Détermination du résultat du combat
   */
  private function determineFightResult(): void {
    if ($this->defenserAttackPoint > $this->attackerDefensePoint) {
      $this->fight->result = "LOSE";
    } elseif ($this->attackerAttackPoint > $this->defenserDefensePoint) {
      $this->fight->result = "WIN";
    } else {
      $this->fight->result = "DRAW";
    }
  }


  /**
   * Application des dommages aux structures de la planète attaquée
   */
  private function setDamagesToDefense(): void {
    /** @var StructureModel[] $defenseStructures */
    $defenseStructures = [];

    /** @var StructureModel[] $resetStructures */
    $resetStructures = [];

    // Pourcentage de structures à détruire
    $percentageDestroyedDefenser = $this->attackerAttackPoint / $this->defenserDefensePoint;

    // Récupération des structures de défense avec level > 1, sur la planète attaquée
    foreach ($this->defensePlanet->structures as $structure) {
      if ($structure->level !== 0 && $structure->defensePoint !== 0) {
        $defenseStructures[] = $structure;
      }
    }

    if (!$defenseStructures) {
      return;
    }

    // Tous les systèmes de défense de la planète attaquée sont détruits
    if ($percentageDestroyedDefenser >= 1) {
      $resetStructures = $defenseStructures;
    }
    // Certains systèmes de défense de la planète attaquée sont détruits
    else {
      $nbStructures = (int) round(count($defenseStructures) * $percentageDestroyedDefenser);

      if ($nbStructures === 0) {
        return;
      }

      $randomIndexes = array_rand($defenseStructures, $nbStructures);

      if ($nbStructures === 1) {
        $resetStructures[] = $defenseStructures[$randomIndexes];
      } else {
        foreach ($randomIndexes as $index) {
          $resetStructures[] = $defenseStructures[$index];
        }
      }
    }

    // Réinitialisation des structures détruites au niveau 0
    $structureController = new StructureController($this->defensePlanet->id);
    $structureController->resetItems($resetStructures);
  }


  /**
   * Application des dommages aux unités de la planète attaquante
   *
   * @param integer[] $attackUnitIds Identifiants des unités attaquantes
   * @return UnitModel[] Unités désactivées
   */
  private function setDamagesToAttack(array $attackUnitIds): array {
    /** @var UnitModel[] $disableUnits */
    $disableUnits = [];

    // Pourcentage d'unités à détruire
    $percentageDestroyedAttacker = $this->defenserAttackPoint / $this->attackerDefensePoint;

    // Toutes les unités de la planète attaquant, envoyées au combat, sont désactivées
    if ($percentageDestroyedAttacker >= 1) {
      foreach ($this->attackPlanet->units as $unit) {
        if (in_array($unit->id, $attackUnitIds) && !$unit->createInProgress) {
          $disableUnits[] = $unit;
        }
      }
    }
    // Certaines unités de la planète attaquant, envoyées au combat, sont désactivées
    else {
      $nbUnits = (int) round(count($attackUnitIds) * $percentageDestroyedAttacker);

      if ($nbUnits === 0) {
        return $disableUnits;
      }

      if ($nbUnits === 1) {
        $randomIndexes = array($attackUnitIds[array_rand($attackUnitIds)]);
      } else {
        $randomIndexes = array_rand($attackUnitIds, $nbUnits);
      }

      // Récupération des unités désactivées
      $disableUnits = array_filter($this->attackPlanet->units, function (UnitModel $unit) use ($randomIndexes) {
        return in_array($unit->id, $randomIndexes) && !$unit->createInProgress;
      });
    }

    // Désactivation des unités
    $unitController = new UnitController($this->attackPlanet->id);
    $unitController->disableUnits($disableUnits);

    return $disableUnits;
  }


  /**
   * Calcul des récompenses en cas de défaite de la planète attaquante
   *
   * @param UnitModel[] $disableUnits Unités désactivées
   */
  private function calculateRewardsOnDefeat(array $disableUnits): void {
    $resourceController = new ResourceController($this->defensePlanet->id);

    // Récupération et mise à jour des ressources sur la planète avant le calcul des récompenses
    $resourceController->getResourcesPlanet();
    $resourceController->updateResourcesPlanet();
    $resourceController->addCostsUnitsToResources($disableUnits);
  }


  /**
   * Calcul des récompenses en cas de victoire de la planète attaquante
   *
   * @param integer[] $attackUnitIds Identifiants des unités attaquantes
   */
  private function calculateRewardsOnVictory(array $attackUnitIds): void {
    // S'il reste au moins une unité de colonisation du côté de l'attaquant, le propriétaire de
    // la planète attaquée devient l'attaquant
    $settlementUnits = array_filter($this->attackPlanet->units, function (UnitModel $unit) use ($attackUnitIds) {
      return in_array($unit->id, $attackUnitIds) && $unit->itemId === "COLONIE" && !$unit->createInProgress;
    });

    if ($settlementUnits) {
      $planetController = new PlanetController();
      $planetController->assignUser($this->defensePlanet->id, $this->attackPlanet->userId);
    } else {
      // S'il reste au moins une unité de transport
      $freightUnits = array_filter($this->attackPlanet->units, function (UnitModel $unit) use ($attackUnitIds) {
        return in_array($unit->id, $attackUnitIds) && $unit->freightCapacity !== 0 && !$unit->createInProgress;
      });

      $freightCapacity = 0;

      foreach ($freightUnits as $freightUnit) {
        $freightCapacity += $freightUnit->freightCapacity;
      }

      // Mise à jour de la planète attaquée
      $resourceController = new ResourceController($this->defensePlanet->id);
      $resourcesLosingPlanet = $resourceController->getResourcesPlanet();
      $resourceController->updateResourcesPlanet();

      // Calcul du nombre de ressources gagnées
      foreach ($resourcesLosingPlanet as $resourceLosingPlanet) {
        $resource = clone $resourceLosingPlanet;
        $resource->bonus = 0;
        $resource->production = 0;
        $resource->quantity = min($freightCapacity, $resourceLosingPlanet->quantity);
        $resource->lastTimeCalc = "";

        $this->fight->acquiredResources[] = $resource;

        $freightCapacity -= $resource->quantity;
      }

      $resourceController->subtractResources($this->fight->acquiredResources);


      // Mise à jour de la planète attaquante
      $resourceController = new ResourceController($this->attackPlanet->id);
      $resourceController->getResourcesPlanet();
      $resourceController->updateResourcesPlanet();
      $resourceController->addResources($this->fight->acquiredResources);
    }
  }
}
