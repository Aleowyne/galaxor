<?php

class PlanetModel extends Database {
  /**
   * Sélection de toutes les planètes en base
   *
   * @return array Liste de toutes les planètes
   */
  public function findAll(): array {
    return $this->select(
      "SELECT p.id AS planet_id, p.name AS planet_name, s.name AS solar_system_name,
              g.name AS galaxy_name, u.name AS universe_name
        FROM planet AS p
        INNER JOIN solar_system AS s
          ON p.solar_system_id = s.id
        INNER JOIN galaxy AS g
          ON s.galaxy_id = g.id
        INNER JOIN universe AS u
          ON g.universe_id = u.id
        ORDER BY p.id ASC"
    );
  }

  /**
   * Sélection de toutes les planètes d'un système solaire en base
   *
   * @param array $params Identifiant du système solaire
   * @return array Liste des planètes
   */
  public function findAllBySolarSystem(array $params): array {
    return $this->select(
      "SELECT id, name FROM planet WHERE solar_system_id = :solar_system_id",
      [$params]
    );
  }

  /**
   * Sélection d'une planète en base
   *
   * @param array $params Identifiant d'une planète
   * @return array Données de la planète
   */
  public function findOne(array $params): array {
    return $this->select(
      "SELECT p.id, p.name AS planet_name, p.position, p.user_id, s.size, b.resource_id,
              b.bonus
        FROM planet AS p
        NATURAL JOIN planet_size AS s
        NATURAL JOIN position_bonus AS b
        WHERE p.id = :id",
      [$params]
    );
  }

  /**
   * Ajout de planètes dans la base
   *
   * @param array $params Liste des informations des planètes
   * @return array Liste des ID des planètes
   */
  public function insertMultiples(array $params): array {
    return $this->insert(
      "INSERT INTO planet (solar_system_id, name, position) VALUES (:solar_system_id, :name, :position)",
      $params
    );
  }

  /**
   * Mise à jour d'une planète dans la base
   *
   * @param array $params Données de la planète
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateOne(array $params): bool {
    return $this->update(
      "UPDATE planet SET user_id = :user_id WHERE id = :id",
      [$params]
    );
  }
}
