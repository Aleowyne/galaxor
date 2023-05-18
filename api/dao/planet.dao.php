<?php

class PlanetDao extends Database {
  /**
   * Sélection de toutes les planètes en base
   *
   * @return PlanetModel[] Liste de toutes les planètes
   */
  public function findAll(): array {
    $result = $this->select(
      "SELECT id, name, position, user_id 
        FROM planet 
        ORDER BY id ASC"
    );

    return array_map(function (array $res) {
      return new PlanetModel($res);
    }, $result);
  }


  /**
   * Sélection de toutes les planètes d'un système solaire en base
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @return array Liste des planètes
   */
  public function findAllBySolarSystem(int $solarSystemId): array {
    $params = [["solar_system_id" => $solarSystemId]];

    $result = $this->select(
      "SELECT id, name, position, user_id 
        FROM planet 
        WHERE solar_system_id = :solar_system_id",
      $params
    );

    return array_map(function (array $res) {
      return new PlanetModel($res);
    }, $result);
  }


  /**
   * Sélection d'une planète en base
   *
   * @param integer $id Identifiant de la planète
   * @return PlanetModel Données de la planète
   */
  public function findOne(int $id): PlanetModel {
    $params = [["id" => $id]];

    $result = $this->select(
      "SELECT DISTINCT p.id, p.name, p.position, p.user_id, ps.size, pb.resource_id, 
              r.name AS resource_name, pb.bonus, pr.quantity, pr.last_time_calc
        FROM planet AS p
        NATURAL JOIN planet_size AS ps
        NATURAL JOIN position_bonus AS pb
        INNER JOIN planet_resource AS pr
          ON p.id = pr.planet_id
        INNER JOIN resource AS r
          ON pb.resource_id = r.id
          AND pr.resource_id = r.id
        WHERE p.id = :id
        ORDER BY pb.resource_id",
      $params
    );

    $planet = new PlanetModel($result[0] ?? []);

    foreach ($result as $res) {
      $resource = new ResourceModel($res);
      array_push($planet->resources, $resource);
    }

    return $planet;
  }


  /**
   * Ajout de planètes dans la base
   *
   * @param integer $solarSystemId Identifiant du système solaire
   * @param string[] $names Nom des planètes
   * @return PlanetModel[] Liste des planètes
   */
  public function insertMultiples(int $solarSystemId, array $names): array {
    $params = array_map(function (string $name) use ($solarSystemId) {
      return [
        "name" => $name,
        "solar_system_id" => $solarSystemId,
        "position" => rand(1, 10)
      ];
    }, $names);

    $result = $this->insert(
      "INSERT INTO planet (solar_system_id, name, position) 
        VALUES (:solar_system_id, :name, :position)",
      $params
    );

    $planets = [];

    // Récupération des ID des planètes
    foreach ($params as $index => $param) {
      $planet = new PlanetModel();

      $planet->id = $result[$index] ?? 0;
      $planet->name = $param["name"];
      $planet->position = $param["position"];

      array_push($planets, $planet);
    }

    return $planets;
  }


  /**
   * Mise à jour d'une planète dans la base
   * 
   * @param integer $planetId Identifiant de la planète
   * @param string $userId Identifiant de l'utilisateur
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateOne(int $planetId, string $userId): bool {
    $params = [[
      "id" => $planetId,
      "user_id" => $userId
    ]];

    return $this->update(
      "UPDATE planet SET user_id = :user_id WHERE id = :id",
      $params
    );
  }
}
