<?php

class GalaxyModel {
  public int $id;
  public string $name;
  /** @var SolarSystemModel[] $solarSystems **/
  public array $solarSystems;

  /**
   * Constructeur
   *
   * @param mixed[] $galaxy Données de la galaxie
   */
  public function __construct(array $galaxy = []) {
    $this->id = $galaxy["id"] ?? 0;
    $this->name = $galaxy["name"] ?? "";
    $this->solarSystems = [];
  }

  /**
   * Transformation des données de la galaxie sous forme de tableau
   *
   * @return mixed[] Données de la galaxie
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "solar_systems" => array_map(function (SolarSystemModel $solarSystem) {
        return $solarSystem->toArray();
      }, $this->solarSystems)
    ];
  }
}
