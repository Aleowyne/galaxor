<?php

namespace App\Models;

class FightModel {
  public int $id;
  public string $timeFight;
  public string $result;
  public int $attackPlanetId;
  public int $defensePlanetId;
  /** @var UnitModel[] $attackUnits */
  public array $attackUnits;
  /** @var UnitModel[] $defenseUnits */
  public array $defenseUnits;
  /** @var StructureModel[] $defenseStructures */
  public array $defenseStructures;
  /** @var ResourceModel[] $acquiredResources */
  public array $acquiredResources;

  /**
   * Constructeur
   *
   * @param mixed[] $fight Données du combat
   */
  public function __construct(array $fight = []) {
    $this->id = $fight["id"] ?? 0;
    $this->timeFight = $fight["time_fight"] ?? "";
    $this->result = $fight["result"] ?? "";
    $this->attackPlanetId = $fight["attack_planet"] ?? 0;
    $this->defensePlanetId = $fight["defense_planet"] ?? 0;
    $this->attackUnits = [];
    $this->defenseUnits = [];
    $this->defenseStructures = [];
    $this->acquiredResources = [];
  }


  /**
   * Transformation des données du combat sous forme de tableau
   *
   * @return mixed[] Données du combat
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "time_fight" => $this->timeFight,
      "result" => $this->result,
      "attack_planet" => $this->attackPlanetId,
      "defense_planet" => $this->defensePlanetId,
      "attack_units" => array_map(function (UnitModel $unit) {
        return $unit->toArrayForFight();
      }, $this->attackUnits),
      "defense_units" => array_map(function (UnitModel $unit) {
        return $unit->toArrayForFight();
      }, $this->defenseUnits),
      "defense_structures" => array_map(function (StructureModel $structure) {
        return $structure->toArrayForFight();
      }, $this->defenseStructures),
      "acquired_resources" => array_map(function (ResourceModel $resource) {
        return $resource->toArrayForFight();
      }, $this->acquiredResources)
    ];
  }
}
