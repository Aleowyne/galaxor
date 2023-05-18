<?php
class ItemDao extends Database {
  /**
   * Sélection des items (structures, recherches, unités) d'une planète en base
   * 
   * @param integer $planetId Identifiant de la planète
   * @return ItemModel[] Items de la planète
   */
  public function findAllByPlanet(int $planetId): array {
    $params = [["planet_id" => $planetId]];

    $result = $this->select(
      "SELECT pi.item_id, pi.level
        FROM planet_item AS pi
        WHERE pi.planet_id = :planet_id
        ORDER BY pi.item_id",
      $params
    );

    return array_map(function (array $res) {
      return new ItemModel($res);
    }, $result);
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
}
