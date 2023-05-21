<?php

class ItemModel {
  public string $id;
  public string $name;
  public string $type;
  public int $level;
  public string|int $buildTime;
  public int $upgradeInProgress;
  public string $timeEndUpgrade;
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
    $this->id = $item["item_id"] ?? "";
    $this->name = $item["item_name"] ?? "";
    $this->type = $item["item_type"] ?? "";
    $this->level = $item["level"] ?? 0;
    $this->buildTime = $item["build_time"] ?? 0;
    $this->upgradeInProgress = $item["upgrade_in_progress"] ?? 0;
    $this->timeEndUpgrade = $item["time_end_upgrade"] ?? "";
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
      "id" => $this->id,
      "name" => $this->name,
      "level" => $this->level,
      "build_time" => $this->buildTime,
      "upgrade_in_progress" => $this->upgradeInProgress,
      "time_end_upgrade" => $this->timeEndUpgrade,
      "costs" => array_map(function (CostModel $cost) {
        return $cost->toArray();
      }, $this->costs),
      "prerequisites" => array_map(function (PrerequisiteModel $prerequisite) {
        return $prerequisite->toArray();
      }, $this->prerequisites)
    ];
  }
}
