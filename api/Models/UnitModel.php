<?php

namespace App\Models;

class UnitModel extends ItemModel {
  public int $id;
  public bool $createInProgress;
  public string $endTimeCreate;
  public int $quantity;

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
    $this->quantity = $unit["quantity"] ?? 0;
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
      "attack_point" => $this->attackPoint,
      "defense_point" => $this->defensePoint,
      "freight_capacity" => $this->freightCapacity,
      "img_filename" => $this->imgFilename,
      "create_in_progress" => $this->createInProgress,
      "end_time_create" => $this->endTimeCreate
    ];
  }


  /**
   * Transformation des données de l'unité sous forme de tableau
   * pour les types d'unités
   *
   * @return mixed[] Données de l'item
   */
  public function toArrayForType(): array {
    return [
      "item_id" => $this->itemId,
      "name" => $this->name,
      "build_time" => $this->buildTime,
      "attack_point" => $this->attackPoint,
      "defense_point" => $this->defensePoint,
      "freight_capacity" => $this->freightCapacity,
      "img_filename" => $this->imgFilename,
      "costs" => array_map(function (CostModel $cost) {
        return $cost->toArray();
      }, $this->costs),
      "prerequisites" => array_map(function (PrerequisiteModel $prerequisite) {
        return $prerequisite->toArray();
      }, $this->prerequisites)
    ];
  }


  /**
   * Transformation des données de l'unité sous forme de tableau
   * pour les combats
   *
   * @return mixed[] Données de l'unité
   */
  public function toArrayForFight(): array {
    return [
      "item_id" => $this->itemId,
      "name" => $this->name,
      "quantity" => $this->quantity
    ];
  }
}
