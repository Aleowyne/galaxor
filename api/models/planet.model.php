<?php

class PlanetModel extends Database {
  /**
   * Sélection de toutes les planètes d'un système solaire en base
   *
   * @return array Liste de toutes les planètes
   */
  public function findAllFromSolarSystem($params): array {
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
      "SELECT id, name, position, user_id FROM planet WHERE id = :id",
      [$params]
    );
  }

  /**
   * Ajout de planètes dans la base
   *
   * @param array $params Liste des informations des planètes
   * @return array Liste des ID des planètes
   */
  public function insertMultiples($params): array {
    return $this->insert(
      "INSERT INTO planet (solar_system_id, name, position) VALUES (:solar_system_id, :name, :position)",
      $params
    );
  }
}
