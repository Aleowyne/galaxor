<?php

class SolarSystemModel extends Database {
  /**
   * Sélection de tous les systèmes solaires d'une galaxie en base
   *
   * @param array $params Identifiant de la galaxie
   * @return array Liste des systèmes solaires
   */
  public function findAllByGalaxy(array $params): array {
    return $this->select(
      "SELECT id, name FROM solar_system WHERE galaxy_id = :galaxy_id",
      [$params]
    );
  }

  /**
   * Sélection d'une système solaire en base
   *
   * @param array $params Identifiant du système solaire
   * @return array Données du système solaire
   */
  public function findOne(array $params): array {
    return $this->select(
      "SELECT id, name FROM solar_system WHERE id = :id",
      [$params]
    );
  }

  /**
   * Ajout de systèmes solaires dans la base
   *
   * @param array $params Liste des informations des systèmes solaires
   * @return array Liste des ID des systèmes solaires
   */
  public function insertMultiples(array $params): array {
    return $this->insert(
      "INSERT INTO solar_system (galaxy_id, name) VALUES (:galaxy_id, :name)",
      $params
    );
  }
}
