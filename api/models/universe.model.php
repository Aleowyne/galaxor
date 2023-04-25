<?php

class UniverseModel extends Database {
  /**
   * Sélection de tous les univers en base
   *
   * @return array Liste de tous les univers
   */
  public function findAll(): array {
    return $this->select("SELECT id, name FROM universe ORDER BY id ASC");
  }

  /**
   * Sélection d'un univers en base
   *
   * @param array $params Identifiant de l'univers
   * @return array Données d'un univers
   */
  public function findOne(array $params): array {
    return $this->select(
      "SELECT id, name FROM universe WHERE id = :id",
      [$params]
    );
  }

  /**
   * Ajout d'un univers dans la base
   *
   * @param array $params Nom de l'univers
   * @return integer ID de l'univers
   */
  public function insertOne($params): int {
    $ids = $this->insert(
      "INSERT INTO universe (name) VALUES (:name)",
      [$params]
    );

    return (count($ids) === 1) ? $ids[0] : 0;
  }
}
