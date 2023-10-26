<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\ItemModel;
use App\Models\CostModel;
use App\Models\PrerequisiteModel;

class ItemDao extends Database {
  /**
   * Sélection d'un item en base
   *
   * @param string $itemId Identifiant de l'item
   * @param string $itemType Type de l'item
   * @return ItemModel Données de l'item
   */
  public function findOneByType(string $itemId, string $itemType = "%"): ItemModel {
    $params = [[
      "item_id" => $itemId,
      "item_type" => $itemType
    ]];

    $result = $this->select(
      "SELECT id AS item_id, name AS item_name, type AS item_type, build_time,
              attack_point, defense_point, freight_capacity, img_filename
        FROM item
        WHERE id = :item_id
          AND type LIKE :item_type",
      $params
    );

    return new ItemModel($result[0] ?? []);
  }


  /**
   * Sélection des items en fonction d'un type d'item en base
   *
   * @param string $itemType Type de l'item
   * @return ItemModel[] Données des items
   */
  public function findAllByType(string $itemType = "%"): array {
    $params = [[
      "item_type" => $itemType
    ]];

    $result = $this->select(
      "SELECT id AS item_id, name AS item_name, type AS item_type, build_time,
              attack_point, defense_point, freight_capacity, img_filename
        FROM item
        WHERE type LIKE :item_type",
      $params
    );

    return array_map(function (array $res) {
      return new ItemModel($res);
    }, $result);
  }


  /**
   * Sélection des items d'une planète en base, en fonction d'un type d'item
   *
   * @param integer $planetId Identifiant de la planète
   * @param string $itemType Type de l'item
   * @return ItemModel[] Items de la planète
   */
  public function findAllByPlanet(int $planetId, string $itemType = "%"): array {
    $params = [[
      "planet_id" => $planetId,
      "item_type" => $itemType
    ]];

    $result = $this->select(
      "SELECT pi.item_id, i.name AS item_name, i.type AS item_type, pi.level,
              pi.upgrade_in_progress, pi.end_time_upgrade, i.build_time,
              i.attack_point, i.defense_point, i.freight_capacity, i.img_filename
        FROM planet_item AS pi
        INNER JOIN item AS i
          ON pi.item_id = i.id
        WHERE pi.planet_id = :planet_id
          AND i.type LIKE :item_type
        ORDER BY pi.item_id",
      $params
    );

    return array_map(function (array $res) {
      return new ItemModel($res);
    }, $result);
  }

  /**
   * Sélection d'un item d'une planète en base
   *
   * @param string $itemId Identifiant de l'item
   * @param integer $planetId Identifiant de la planète
   * @param string $itemType Type de l'item
   * @return ItemModel Données de l'item de la planète
   */
  public function findOneByPlanet(string $itemId, int $planetId, string $itemType = "%"): ItemModel {
    $params = [[
      "planet_id" => $planetId,
      "item_id" => $itemId,
      "item_type" => $itemType
    ]];

    $result = $this->select(
      "SELECT pi.item_id, i.name AS item_name, i.type AS item_type, pi.level,
              pi.upgrade_in_progress, pi.end_time_upgrade, i.build_time,
              i.attack_point, i.defense_point, i.freight_capacity, i.img_filename
        FROM planet_item AS pi
        INNER JOIN item AS i
          ON pi.item_id = i.id
        WHERE pi.planet_id = :planet_id
          AND pi.item_id = :item_id
          AND i.type LIKE :item_type",
      $params
    );

    return new ItemModel($result[0] ?? []);
  }


  /**
   * Sélection du coût des items pour prendre un niveau, en base
   *
   * @return CostModel[] Coût des items
   */
  public function findCosts(): array {
    $result = $this->select(
      "SELECT ic.item_id, ic.resource_id, r.name AS resource_name, ic.quantity
        FROM item_cost AS ic
          INNER JOIN resource AS r
          ON ic.resource_id = r.id
        ORDER BY ic.item_id, ic.resource_id"
    );

    return array_map(function (array $res) {
      return new CostModel($res);
    }, $result);
  }


  /**
   * Sélection des pré-requis pour construire (niveau 1) un item en base
   *
   * @return PrerequisiteModel[] Pré-requis des items
   */
  public function findPrerequisites(): array {
    $result = $this->select(
      "SELECT ip.item_id, ip.required_item_id, i.name AS required_item_name, ip.level
        FROM item_prerequisite AS ip
          INNER JOIN item AS i
          ON ip.required_item_id = i.id
        ORDER BY ip.item_id, ip.required_item_id"
    );

    return array_map(function (array $res) {
      return new PrerequisiteModel($res);
    }, $result);
  }


  /**
   * Mise à jour d'un item d'une planète dans la base
   *
   * @param integer $planetId Identifiant de la planète
   * @param ItemModel $item Données de l'item
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateOneByPlanet(int $planetId, ItemModel $item): bool {
    $params = [[
      "planet_id" => $planetId,
      "item_id" => $item->itemId,
      "level" => $item->level,
      "upgrade_in_progress" => (int) $item->upgradeInProgress,
      "end_time_upgrade" => $item->endTimeUpgrade
    ]];

    return $this->update(
      "UPDATE planet_item
        SET level = :level,
            upgrade_in_progress = :upgrade_in_progress,
            end_time_upgrade = :end_time_upgrade
        WHERE planet_id = :planet_id
          AND item_id = :item_id",
      $params
    );
  }


  /**
   * Mise à jour d'items d'une planète dans la base
   *
   * @param integer $planetId Identifiant de la planète
   * @param ItemModel[] $items Données des items
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateMultiplesByPlanet(int $planetId, array $items): bool {
    $params = array_map(function (ItemModel $item) use ($planetId) {
      return [
        "planet_id" => $planetId,
        "item_id" => $item->itemId,
        "level" => $item->level,
        "upgrade_in_progress" => (int) $item->upgradeInProgress,
        "end_time_upgrade" => $item->endTimeUpgrade
      ];
    }, $items);

    return $this->update(
      "UPDATE planet_item
        SET level = :level,
            upgrade_in_progress = :upgrade_in_progress,
            end_time_upgrade = :end_time_upgrade
        WHERE planet_id = :planet_id
          AND item_id = :item_id",
      $params
    );
  }
}
