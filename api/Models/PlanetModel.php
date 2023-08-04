<?php

namespace App\Models;

class PlanetModel {
  public int $id;
  public string $name;
  public int $position;
  public int $userId;
  public string $username;
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
    $this->username = $planet["username"] ?? "";
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
    return [
      "id" => $this->id,
      "name" => $this->name,
      "position" => $this->position,
      "user_id" => $this->userId,
      "username" => $this->username
    ];
  }
}
