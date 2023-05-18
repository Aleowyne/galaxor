<?php

class ResourceDao extends Database {
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
