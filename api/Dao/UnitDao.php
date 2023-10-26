<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\UnitModel;

class UnitDao extends Database {
  /**
   * Sélection d'une unité d'une planète en base
   *
   * @param integer $unitId Identifiant de l'unité
   * @return UnitModel Données de l'item de la planète
   */
  public function findOne(int $unitId): UnitModel {
    $params = [[
      "id" => $unitId
    ]];

    $result = $this->select(
      "SELECT pu.id, pu.item_id, i.name AS item_name, i.type AS item_type,
              pu.create_in_progress, pu.end_time_create, i.build_time,
              i.attack_point, i.defense_point, i.freight_capacity, i.img_filename
        FROM planet_unit AS pu
        INNER JOIN item AS i
          ON pu.item_id = i.id
        WHERE pu.id = :id
          AND pu.active = 1",
      $params
    );

    return new UnitModel($result[0] ?? []);
  }


  /**
   * Sélection des unités d'une planète en base
   *
   * @param string $planetId Identifiant de la planète
   * @return UnitModel[] Données des unités de la planète
   */
  public function findAllByPlanet(string $planetId): array {
    $params = [[
      "planet_id" => $planetId
    ]];

    $result = $this->select(
      "SELECT pu.id, pu.item_id, i.name AS item_name, i.type AS item_type,
              pu.create_in_progress, pu.end_time_create, i.build_time,
              i.attack_point, i.defense_point, i.freight_capacity, i.img_filename
        FROM planet_unit AS pu
        INNER JOIN item AS i
          ON pu.item_id = i.id
        WHERE pu.planet_id = :planet_id
          AND pu.active = 1",
      $params
    );

    return array_map(function (array $res) {
      return new UnitModel($res);
    }, $result);
  }


  /**
   * Ajout d'une unité à une planète dans la base
   *
   * @param integer $planetId Identifiant de la planète
   * @param UnitModel $unit Données de l'unité
   * @return integer Identifiant de l'unité ajoutée
   */
  public function insertOneByPlanet(int $planetId, UnitModel $unit): int {
    $params = [[
      "planet_id" => $planetId,
      "item_id" => $unit->itemId,
      "create_in_progress" => $unit->createInProgress,
      "end_time_create" => $unit->endTimeCreate
    ]];

    $result = $this->insert(
      "INSERT INTO planet_unit (planet_id, item_id, create_in_progress, end_time_create)
        VALUES (:planet_id, :item_id, :create_in_progress, :end_time_create)",
      $params
    );

    return $result[0] ?? 0;
  }


  /**
   * Mise à jour d'un item d'une planète dans la base
   *
   * @param integer $planetId Identifiant de la planète
   * @param UnitModel $unit Données de l'unité
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateOneByPlanet(int $planetId, UnitModel $unit): bool {
    $params = [[
      "planet_id" => $planetId,
      "item_id" => $unit->itemId,
      "create_in_progress" => (int) $unit->createInProgress
    ]];

    return $this->update(
      "UPDATE planet_unit
        SET create_in_progress = :create_in_progress
        WHERE planet_id = :planet_id
          AND item_id = :item_id",
      $params
    );
  }


  /**
   * Désactivation d'unités dans la base
   *
   * @param UnitModel[] $units Liste des unités à désactiver
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function deactivateMultiples(array $units): bool {
    $params = array_map(function (UnitModel $unit) {
      return [
        "id" => $unit->id
      ];
    }, $units);

    return $this->update(
      "UPDATE planet_unit
        SET active = FALSE
        WHERE id = :id",
      $params
    );
  }
}
