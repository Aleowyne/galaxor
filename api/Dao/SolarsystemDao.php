<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\SolarsystemModel;

class SolarsystemDao extends Database {
  /**
   * Sélection de tous les systèmes solaires d'une galaxie en base
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @return SolarsystemModel[] Liste des systèmes solaires
   */
  public function findAllByGalaxy(int $galaxyId): array {
    $params = [["galaxy_id" => $galaxyId]];

    $result =  $this->select(
      "SELECT id, name FROM solar_system WHERE galaxy_id = :galaxy_id",
      $params
    );

    return array_map(function (array $res) {
      return new SolarsystemModel($res);
    }, $result);
  }


  /**
   * Sélection d'une système solaire en base
   *
   * @param integer $id Identifiant du système solaire
   * @return SolarsystemModel Données du système solaire
   */
  public function findOne(int $id): SolarsystemModel {
    $params = [["id" => $id]];

    $result = $this->select(
      "SELECT id, name FROM solar_system WHERE id = :id",
      $params
    );

    return new SolarsystemModel($result[0] ?? []);
  }


  /**
   * Ajout de systèmes solaires dans la base
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @param string[] $names Nom des systèmes solaires
   * @return SolarsystemModel[] Liste des systèmes solaires
   */
  public function insertMultiplesByGalaxy(int $galaxyId, array $names): array {
    $params = array_map(function (string $name) use ($galaxyId) {
      return [
        "name" => $name,
        "galaxy_id" => $galaxyId
      ];
    }, $names);

    $result = $this->insert(
      "INSERT INTO solar_system (galaxy_id, name) VALUES (:galaxy_id, :name)",
      $params
    );

    $solarSystems = [];

    // Récupération des ID des systèmes solaires
    foreach ($names as $index => $name) {
      $solarSystem = new SolarsystemModel();

      $solarSystem->id = $result[$index] ?? 0;
      $solarSystem->name = $name;

      $solarSystems[] = $solarSystem;
    }

    return $solarSystems;
  }
}
