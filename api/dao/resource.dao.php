<?php

class ResourceDao extends Database {
  /**
   * Sélection des ressources d'une planète en base
   * 
   * @param integer $planetId Identifiant de la planète
   * @return ResourceModel[] Ressources de la planète
   */
  public function findAllByPlanet(int $planetId): array {
    $params = [["planet_id" => $planetId]];

    $result = $this->select(
      "SELECT DISTINCT pr.resource_id, r.name AS resource_name, pb.bonus, pr.quantity, pr.last_time_calc
        FROM planet AS p
        NATURAL JOIN planet_size AS ps
        NATURAL JOIN position_bonus AS pb
        INNER JOIN planet_resource AS pr
          ON p.id = pr.planet_id
        INNER JOIN resource AS r
          ON pb.resource_id = r.id
          AND pr.resource_id = r.id
        WHERE p.id = :planet_id
        ORDER BY pr.resource_id",
      $params
    );

    return array_map(function (array $res) {
      return new ResourceModel($res);
    }, $result);
  }

  /**
   * Mise à jour des ressources d'une planète dans la base
   *
   * @param integer $planetId Identifiant de la planète
   * @param ResourceModel[] $resources Ressources
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateMultiples(int $planetId, array $resources): bool {
    $params = array_map(function (ResourceModel $resource) use ($planetId) {
      return [
        "planet_id" => $planetId,
        "resource_id" => $resource->id,
        "quantity" => $resource->quantity,
        "last_time_calc" => $resource->lastTimeCalc
      ];
    }, $resources);

    return $this->update(
      "UPDATE planet_resource 
        SET quantity = :quantity,
            last_time_calc = :last_time_calc
        WHERE planet_id = :planet_id
          AND resource_id = :resource_id",
      $params
    );
  }
}
