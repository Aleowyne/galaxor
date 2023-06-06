<?php

namespace App\Models;

class ItemModel {
  public string $itemId;
  public string $name;
  public string $type;
  public int $level;
  public string|int $buildTime;
  public string|int $attackPoint;
  public string|int $defensePoint;
  public string|int $freightCapacity;
  public bool $upgradeInProgress;
  public string $endTimeUpgrade;
  /** @var CostModel[] $costs **/
  public array $costs;
  /** @var PrerequisiteModel[] $prerequisites **/
  public array $prerequisites;

  /**
   * Constructeur
   *
   * @param mixed[] $item Données de l'item
   */
  public function __construct(array $item = []) {
    $this->itemId = $item["item_id"] ?? "";
    $this->name = $item["item_name"] ?? "";
    $this->type = $item["item_type"] ?? "";
    $this->level = $item["level"] ?? 0;
    $this->buildTime = $item["build_time"] ?? 0;
    $this->attackPoint = $item["attack_point"] ?? 0;
    $this->defensePoint = $item["defense_point"] ?? 0;
    $this->freightCapacity = $item["freight_capacity"] ?? 0;
    $this->upgradeInProgress = $item["upgrade_in_progress"] ?? false;
    $this->endTimeUpgrade = $item["end_time_upgrade"] ?? "";
    $this->costs = [];
    $this->prerequisites = [];
  }

  /**
   * Transformation des données de l'item sous forme de tableau
   *
   * @return mixed[] Données de l'item
   */
  public function toArray(): array {
    return [
      "item_id" => $this->itemId,
      "name" => $this->name,
      "level" => $this->level,
      "build_time" => $this->buildTime,
      "attack_point" => $this->attackPoint,
      "defense_point" => $this->defensePoint,
      "freight_capacity" => $this->freightCapacity,
      "upgrade_in_progress" => $this->upgradeInProgress,
      "end_time_upgrade" => $this->endTimeUpgrade,
      "costs" => array_map(function (CostModel $cost) {
        return $cost->toArray();
      }, $this->costs),
      "prerequisites" => array_map(function (PrerequisiteModel $prerequisite) {
        return $prerequisite->toArray();
      }, $this->prerequisites)
    ];
  }
}
