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
      "SELECT pi.item_id, i.name AS item_name, pi.level, 
              pi.upgrade_in_progress, pi.time_end_upgrade, i.build_time,
              ip.resource_id, ip.production
        FROM planet_item AS pi
        INNER JOIN item AS i
          ON pi.item_id = i.id
        LEFT JOIN item_production AS ip
          ON pi.item_id = ip.item_id
        WHERE pi.planet_id = :planet_id
          AND i.type = 'STRUCTURE'
        ORDER BY pi.item_id, ip.resource_id",
      $params
    );

    $currentId = 0;
    $structures = [];
    $structure = new StructureModel();

    foreach ($result as $res) {
      if ($currentId !== $res["item_id"]) {
        if ($currentId) {
          $structures[] = $structure;
        }

        $structure = new StructureModel($res);
      }

      $production = new ProductionModel($res);

      if ($production->resourceId) {
        $structure->formulasProd[] = $production;
      }
      $currentId = $res["item_id"];
    }

    if ($currentId) {
      $structures[] = $structure;
    }

    return $structures;
  }
}
