<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\StructureModel;
use App\Models\ProductionModel;

class StructureDao extends Database {
  /**
   * Sélection des structures d'une planète en base
   *
   * @param integer $planetId Identifiant de la planète
   * @return StructureModel[] Structures de la planète
   */
  public function findAllByPlanet(int $planetId): array {
    $params = [[
      "planet_id" => $planetId
    ]];

    $result = $this->select(
      "SELECT pi.item_id, i.name AS item_name, pi.level,
              pi.upgrade_in_progress, pi.end_time_upgrade, i.build_time,
              i.attack_point, i.defense_point, i.img_filename,
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

    $structures = [];
    /** @var StructureModel $structure **/
    $structure = null;

    foreach ($result as $res) {
      if (!$structure || $structure->itemId !== $res["item_id"]) {
        if ($structure) {
          $structures[] = $structure;
        }

        $structure = new StructureModel($res);
      }

      $production = new ProductionModel($res);

      if ($production->resourceId) {
        $structure->formulasProd[] = $production;
      }
    }

    if ($structure) {
      $structures[] = $structure;
    }

    return $structures;
  }
}
