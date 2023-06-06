<?php

namespace App\Models;

class UniverseModel {
  public int $id;
  public string $name;
  /** @var GalaxyModel[] $galaxies **/
  public array $galaxies;

  /**
   * Constructeur
   *
   * @param mixed[] $universe Données de l'univers
   */
  public function __construct(array $universe = []) {
    $this->id = $universe["id"] ?? 0;
    $this->name = $universe["name"] ?? "";
    $this->galaxies = [];
  }

  /**
   * Transformation des données de l'univers sous forme de tableau
   *
   * @return mixed[] Données de l'univers
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "galaxies" => array_map(function (GalaxyModel $galaxy) {
        return $galaxy->toArray();
      }, $this->galaxies)
    ];
  }
}
