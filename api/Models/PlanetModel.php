<?php

namespace App\Models;

class PlanetModel {
  public int $id;
  public string $name;
  public int $position;
  public int $userId;
  /** @var ResourceModel[] $resources **/
  public array $resources;
  /** @var StructureModel[] $structures **/
  public array $structures;
  /** @var ResearchModel[] $structures **/
  public array $researches;
  /** @var UnitModel[] $units **/
  public array $units;
  /** @var FightModel[] $fights **/
  public array $fights;

  /**
   * Constructeur
   *
   * @param mixed[] $planet Données de la planète
   */
  public function __construct(array $planet = []) {
    $this->id = $planet["id"] ?? 0;
    $this->name = $planet["name"] ?? "";
    $this->position = $planet["position"] ?? 0;
    $this->userId = $planet["user_id"] ?? 0;
    $this->resources = [];
    $this->structures = [];
    $this->researches = [];
    $this->units = [];
    $this->fights = [];
  }


  /**
   * Transformation des données de la planète sous forme de tableau
   *
   * @return mixed[] Données de la planète
   */
  public function toArray(): array {
    $arrayPlanet = [
      "id" => $this->id,
      "name" => $this->name,
      "position" => $this->position,
      "user_id" => $this->userId
    ];

    // Ressources
    if ($this->resources) {
      $resourcesList = [
        "resources" => array_map(function (ResourceModel $resource) {
          return $resource->toArray();
        }, $this->resources)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $resourcesList);
    }

    // Structures
    if ($this->structures) {
      $structuresList = [
        "structures" => array_map(function (StructureModel $structure) {
          return $structure->toArray();
        }, $this->structures)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $structuresList);
    }

    // Recherches
    if ($this->researches) {
      $researchesList = [
        "researches" => array_map(function (ResearchModel $research) {
          return $research->toArray();
        }, $this->researches)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $researchesList);
    }

    // Unités
    if ($this->units) {
      $unitsList = [
        "units" => array_map(function (UnitModel $unit) {
          return $unit->toArray();
        }, $this->units)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $unitsList);
    }

    // Combats
    if ($this->fights) {
      $fightsList = [
        "fights" => array_map(function (FightModel $fight) {
          return $fight->toArray();
        }, $this->fights)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $fightsList);
    }

    return $arrayPlanet;
  }
}
