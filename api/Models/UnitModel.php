<?php

namespace App\Models;

class UnitModel extends ItemModel {
  public int $id;
  public bool $createInProgress;
  public string $endTimeCreate;

  /**
   * Constructeur
   *
   * @param mixed[] $unit Données de l'unité
   */
  public function __construct(array $unit = []) {
    parent::__construct($unit);
    $this->id = $unit["id"] ?? 0;
    $this->createInProgress = $unit["create_in_progress"] ?? false;
    $this->endTimeCreate = $unit["end_time_create"] ?? "";
  }


  /**
   * Transformation des données de l'unité sous forme de tableau
   *
   * @return mixed[] Données de l'unité
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "item_id" => $this->itemId,
      "name" => $this->name,
      "build_time" => $this->buildTime,
      "attack_point" => $this->attackPoint,
      "defense_point" => $this->defensePoint,
      "freight_capacity" => $this->freightCapacity,
      "create_in_progress" => $this->createInProgress,
      "end_time_create" => $this->endTimeCreate
    ];
  }
}
