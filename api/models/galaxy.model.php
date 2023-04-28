<?php

class GalaxyModel extends Database {
  /**
   * Sélection de toutes les galaxies d'un univers en base
   *
   * @param array $params Identifiant de l'univers
   * @return array Liste des galaxies
   */
  public function findAllByUniverse(array $params): array {
    return $this->select(
      "SELECT id, name FROM galaxy WHERE universe_id = :universe_id",
      [$params]
    );
  }

  /**
   * Sélection d'une galaxie en base
   *
   * @param array $params Identifiant de la galaxie
   * @return array Données de la galaxie
   */
  public function findOne(array $params): array {
    return $this->select(
      "SELECT id, name FROM galaxy WHERE id = :id",
      [$params]
    );
  }

  /**
   * Ajout de galaxies dans la base
   *
   * @param array $params Liste des informations des galaxies
   * @return array Liste des ID des galaxies
   */
  public function insertMultiples(array $params): array {
    return $this->insert(
      "INSERT INTO galaxy (universe_id, name) VALUES (:universe_id, :name)",
      $params
    );
  }
}
