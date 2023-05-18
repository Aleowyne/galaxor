<?php

class StructureDao extends Database {
  /**
   * Sélection des structures d'une planète en base
   * 
   * @param integer $planetId Identifiant de la planète
   * @return StructureModel[] Structures de la planète
   */
  public function findAllByPlanet(int $planetId): array {
    $params = [["planet_id" => $planetId]];

    $result = $this->select(
      "SELECT i.id AS item_id, i.name AS item_name, pi.level, 
              pi.upgrade_in_progress, pi.time_end_upgrade, i.build_time,
              ip.resource_id, ip.production
        FROM planet_item AS pi
        INNER JOIN item AS i
          ON pi.item_id = i.id
        LEFT JOIN item_production AS ip
          ON pi.item_id = ip.item_id
        WHERE pi.planet_id = :planet_id
          AND i.type = 'STRUCTURE'
        ORDER BY i.id, ip.resource_id",
      $params
    );

    $currentId = 0;
    $structures = [];
    $structure = new StructureModel();

    foreach ($result as $res) {
      if ($currentId !== $res["item_id"]) {
        if ($currentId) {
          array_push($structures, $structure);
        }

        $structure = new StructureModel($res);
      }

      $production = new ProductionModel($res);
      array_push($structure->formulasProd, $production);
      $currentId = $res["item_id"];
    }

    if ($currentId) {
      array_push($structures, $structure);
    }

    return $structures;
  }


  /**
   * Mise à jour d'une structure d'une planète dans la base
   *
   * @param integer $planetId Identifiant de la planète
   * @param StructureModel $structure Données de la structure
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateOne(int $planetId, StructureModel $structure): bool {
    $params = [[
      "planet_id" => $planetId,
      "item_id" => $structure->id,
      "level" => $structure->level,
      "upgrade_in_progress" => $structure->upgradeInProgress,
      "time_end_upgrade" => $structure->timeEndUpgrade
    ]];

    return $this->update(
      "UPDATE planet_item
        SET level = :level,
            upgrade_in_progress = :upgrade_in_progress,
            time_end_upgrade = :time_end_upgrade
        WHERE planet_id = :planet_id
          AND item_id = :item_id",
      $params
    );
  }
}
