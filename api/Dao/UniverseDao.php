<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\UniverseModel;

class UniverseDao extends Database {
  /**
   * Sélection de tous les univers en base
   *
   * @return UniverseModel[] Liste de tous les univers
   */
  public function findAll(): array {
    $result = $this->select(
      "SELECT id, name FROM universe ORDER BY id ASC"
    );

    return array_map(function (array $res) {
      return new UniverseModel($res);
    }, $result);
  }


  /**
   * Sélection d'un univers en base
   *
   * @param integer $id Identifiant de l'univers
   * @return UniverseModel Données d'un univers
   */
  public function findOne(int $id): UniverseModel {
    $params = [["id" => $id]];

    $result = $this->select(
      "SELECT id, name FROM universe WHERE id = :id",
      $params
    );

    return new UniverseModel($result[0] ?? []);
  }


  /**
   * Ajout d'un univers dans la base
   *
   * @param UniverseModel $user Données de l'univers
   * @return UniverseModel Données de l'univers
   */
  public function insertOne(UniverseModel $universe): UniverseModel {
    $params = [["name" => $universe->name]];

    $result = $this->insert(
      "INSERT INTO universe (name) VALUES (:name)",
      $params
    );

    $universe->id = $result[0] ?? 0;

    return $universe;
  }
}
