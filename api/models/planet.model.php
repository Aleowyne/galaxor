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
   * Récupération de la ressource de la planète
   *
   * @param integer $resourceId Identifiant de la ressource
   * @return ResourceModel Données de la ressource
   */
  public function getResource(int $resourceId): ResourceModel {
    return current(array_filter($this->resources, function (ResourceModel $resource) use ($resourceId) {
      return ($resource->id === $resourceId);
    }));
  }


  /**
   * Récupération de la structure de la planète
   *
   * @param string $structureId Identifiant de la structure
   * @return StructureModel Données de la structure
   */
  public function getStructure(string $structureId): StructureModel {
    return current(array_filter($this->structures, function (StructureModel $structure) use ($structureId) {
      return ($structure->id === $structureId);
    }));
  }


  /**
   * Transformation des données de la planète sous forme de tableau :
   * Identifiant, nom, position et id propriétaire
   *
   * @return mixed[] Données de la planète
   */
  public function toSimpleArray(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "position" => $this->position,
      "user_id" => $this->userId
    ];
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
      "resources" => array_map(function (ResourceModel $resource) {
        return $resource->toArray();
      }, $this->resources),
      "structures" => array_map(function (StructureModel $structure) {
        return $structure->toArray();
      }, $this->structures),
    ];
  }

  /**
   * Transformation des données de la planète sous forme de tableau :
   * Ressources de la planète
   *
   * @return mixed[] Données de la planète
   */
  public function toResourcesArray(): array {
    return [
      "resources" => array_map(function (ResourceModel $resource) {
        return $resource->toArray();
      }, $this->resources)
    ];
  }
}
