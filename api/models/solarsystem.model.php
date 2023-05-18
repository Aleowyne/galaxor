<?php

class SolarSystemModel {
  public int $id;
  public string $name;
  /** @var PlanetModel[] $planets **/
  public array $planets;

  /**
   * Constructeur
   *
   * @param mixed[] $solarSystem Données du système solaire
   */
  public function __construct(array $solarSystem = []) {
    $this->id = $solarSystem["id"] ?? 0;
    $this->name = $solarSystem["name"] ?? "";
    $this->planets = [];
  }

  /**
   * Transformation des données du système solaire sous forme de tableau
   *
   * @return mixed[] Données du système solaire
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "planets" => array_map(function (PlanetModel $planet) {
        return $planet->toSimpleArray();
      }, $this->planets)
    ];
  }
}
