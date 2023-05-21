<?php

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
  public array $units;

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
      $resources = [
        "resources" => array_map(function (ResourceModel $resource) {
          return $resource->toArray();
        }, $this->resources)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $resources);
    }

    // Structures
    if ($this->structures) {
      $structures = [
        "structures" => array_map(function (StructureModel $structure) {
          return $structure->toArray();
        }, $this->structures)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $structures);
    }

    // Recherches
    if ($this->researches) {
      $researches = [
        "researches" => array_map(function (ResearchModel $research) {
          return $research->toArray();
        }, $this->researches)
      ];

      $arrayPlanet = array_merge($arrayPlanet, $researches);
    }

    return $arrayPlanet;
  }
}
