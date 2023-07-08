<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\FightModel;
use App\Models\ResourceModel;
use App\Models\UnitModel;
use App\Models\StructureModel;

class FightDao extends Database {
  /**
   * Sélection des combats d"une planète en base
   *
   * @param integer $planetId Identifiant de la planète
   * @return FightModel[] Combats de la planète
   */
  public function findAllByPlanet(int $planetId): array {
    $params = [[
      "planet_id" => $planetId
    ]];

    $result = $this->select(
      "SELECT f.id, f.attack_planet, f.defense_planet, f.time_fight, f.result,
              fr.resource_id, r.name AS resource_name, fr.quantity
        FROM fight AS f
        LEFT JOIN fight_resource AS fr
          ON f.id = fr.fight_id
        LEFT JOIN resource AS r
          ON fr.resource_id = r.id
        WHERE f.attack_planet = :planet_id
           OR f.defense_planet = :planet_id
        ORDER BY f.id, fr.resource_id",
      $params
    );

    $params = [];
    $fights = [];
    /** @var FightModel $fight **/
    $fight = null;

    foreach ($result as $res) {
      if (!$fight || $fight->id !== $res["id"]) {
        if ($fight) {
          $fights[] = $fight;
        }

        $fight = new FightModel($res);

        $params[] = [
          "id" => $fight->id
        ];
      }

      $resourceId = $res["resource_id"] ?? "";

      if ($resourceId) {
        $fight->acquiredResources[] = new ResourceModel($res);
      }
    }

    if ($fight) {
      $fights[] = $fight;
    }

    if (empty($params)) {
      return $fights;
    }

    // Recherche des unités présentes lors du combat
    $result = $this->select(
      "SELECT fu.fight_id, pu.planet_id, pu.item_id, i.name AS item_name, COUNT(i.name) AS quantity
        FROM fight_unit AS fu
        INNER JOIN planet_unit AS pu
          ON fu.unit_id = pu.id
        INNER JOIN item AS i
          ON pu.item_id = i.id
        WHERE fu.fight_id = :id
        GROUP BY fu.fight_id, pu.planet_id, pu.item_id, i.name
        ORDER BY fu.fight_id, pu.item_id",
      $params
    );

    foreach ($result as $res) {
      $searchFight = array_filter($fights, function (FightModel $fight) use ($res) {
        return $fight->id === $res["fight_id"];
      });

      /** @var FightModel $fight **/
      $fight = $searchFight ? current($searchFight) : new FightModel();

      $unit = new UnitModel($res);

      // Unités attaquantes
      if ($res["planet_id"] === $fight->attackPlanetId) {
        $fight->attackUnits[] = $unit;
      }
      // Unités défensives
      else {
        $fight->defenseUnits[] = $unit;
      }
    }

    // Recherche des structures présentes lors du combat
    $result = $this->select(
      "SELECT fi.fight_id, fi.item_id, i.name AS item_name, COUNT(i.name) AS quantity
        FROM fight_item AS fi
        LEFT JOIN item AS i
          ON fi.item_id = i.id
        WHERE fi.fight_id = :id
        GROUP BY fi.fight_id, fi.item_id, i.name
        ORDER BY fi.fight_id, fi.item_id",
      $params
    );

    foreach ($result as $res) {
      $searchFight = array_filter($fights, function (FightModel $fight) use ($res) {
        return $fight->id === $res["fight_id"];
      });

      /** @var FightModel $fight **/
      $fight = $searchFight ? current($searchFight) : new FightModel();

      $structure = new StructureModel($res);
      $fight->defenseStructures[] = $structure;
    }

    return $fights;
  }


  /**
   * Sélection d'un combat en base
   *
   * @param integer $fightId Identifiant du combat
   * @return FightModel Données du combat
   */
  public function findOne(int $fightId): FightModel {
    $params = [[
      "id" => $fightId
    ]];

    $result = $this->select(
      "SELECT f.id, f.attack_planet, f.defense_planet, f.time_fight, f.result,
              fr.resource_id, r.name AS resource_name, fr.quantity
        FROM fight AS f
        LEFT JOIN fight_resource AS fr
          ON f.id = fr.fight_id
        LEFT JOIN resource AS r
          ON fr.resource_id = r.id
        WHERE f.id = :id
        ORDER BY fr.resource_id",
      $params
    );

    /** @var FightModel $fight **/
    $fight = null;

    foreach ($result as $res) {
      if (!$fight) {
        $fight = new FightModel($res);
      }

      $resourceId = $res["resource_id"] ?? "";

      if ($resourceId) {
        $fight->acquiredResources[] = new ResourceModel($res);
      }
    }

    if ($fight->id === 0) {
      return $fight;
    }

    // Recherche des unités présentes lors du combat
    $result = $this->select(
      "SELECT fu.fight_id, pu.planet_id, pu.item_id, i.name AS item_name, COUNT(i.name) AS quantity
        FROM fight_unit AS fu
        INNER JOIN planet_unit AS pu
          ON fu.unit_id = pu.id
        INNER JOIN item AS i
          ON pu.item_id = i.id
        WHERE fu.fight_id = :id
        GROUP BY fu.fight_id, pu.planet_id, pu.item_id, i.name
        ORDER BY fu.fight_id, pu.item_id",
      $params
    );

    foreach ($result as $res) {
      $unit = new UnitModel($res);

      // Unités attaquantes
      if ($res["planet_id"] === $fight->attackPlanetId) {
        $fight->attackUnits[] = $unit;
      }
      // Unités défensives
      else {
        $fight->defenseUnits[] = $unit;
      }
    }

    // Recherche des structures présentes lors du combat
    $result = $this->select(
      "SELECT fi.fight_id, fi.item_id, i.name AS item_name, COUNT(i.name) AS quantity
        FROM fight_item AS fi
        INNER JOIN item AS i
          ON fi.item_id = i.id
        WHERE fi.fight_id = :id
        GROUP BY fi.fight_id, fi.item_id, i.name
        ORDER BY fi.fight_id, fi.item_id",
      $params
    );

    $fight->defenseStructures = array_map(function (array $res) {
      return new StructureModel($res);
    }, $result);

    return $fight;
  }


  /**
   * Ajout d'un combat dans la base
   *
   * @param FightModel $fight Données du combat
   * @return integer Identifiant du combat
   */
  public function insertOne(FightModel $fight): int {
    // Ajout du combat
    $params = [[
      "attack_planet" => $fight->attackPlanetId,
      "defense_planet" => $fight->defensePlanetId,
      "time_fight" => $fight->timeFight,
      "result" => $fight->result
    ]];

    $result = $this->insert(
      "INSERT INTO fight (attack_planet, defense_planet, time_fight, result)
        VALUES (:attack_planet, :defense_planet, :time_fight, :result)",
      $params
    );

    $fight->id = $result[0] ?? 0;
    $params = [];


    // Ajout des ressources gagnées lors du combat
    if ($fight->acquiredResources) {
      $params = array_map(function (ResourceModel $resource) use ($fight) {
        return [
          "fight_id" => $fight->id,
          "resource_id" => $resource->id,
          "quantity" => $resource->quantity
        ];
      }, $fight->acquiredResources);

      $this->insert(
        "INSERT INTO fight_resource (fight_id, resource_id, quantity)
        VALUES (:fight_id, :resource_id, :quantity)",
        $params
      );

      $params = [];
    }

    // Ajout des unités qui sont intervenues lors du combat
    if ($fight->attackUnits) {
      $params = array_map(function (UnitModel $unit) use ($fight) {
        return [
          "fight_id" => $fight->id,
          "unit_id" => $unit->id
        ];
      }, $fight->attackUnits);
    }

    if ($fight->defenseUnits) {
      $params = array_merge($params, array_map(function (UnitModel $unit) use ($fight) {
        return [
          "fight_id" => $fight->id,
          "unit_id" => $unit->id
        ];
      }, $fight->defenseUnits));
    }

    if ($params) {
      $this->insert(
        "INSERT INTO fight_unit (fight_id, unit_id)
        VALUES (:fight_id, :unit_id)",
        $params
      );

      $params = [];
    }


    if ($fight->defenseStructures) {
      // Ajout des structures qui sont intervenues lors du combat
      $params = array_map(function (StructureModel $structure) use ($fight) {
        return [
          "fight_id" => $fight->id,
          "planet_id" => $fight->defensePlanetId,
          "item_id" => $structure->itemId
        ];
      }, $fight->defenseStructures);

      $this->insert(
        "INSERT INTO fight_item (fight_id, planet_id, item_id)
        VALUES (:fight_id, :planet_id, :item_id)",
        $params
      );
    }

    return $fight->id;
  }
}
