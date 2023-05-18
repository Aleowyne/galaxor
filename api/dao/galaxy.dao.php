<?php

class GalaxyDao extends Database {
  /**
   * Sélection de toutes les galaxies d'un univers en base
   *
   * @param integer $universeId Identifiant de l'univers
   * @return GalaxyModel[] Liste des galaxies
   */
  public function findAllByUniverse(int $universeId): array {
    $params = [["universe_id" => $universeId]];

    $result = $this->select(
      "SELECT id, name FROM galaxy WHERE universe_id = :universe_id",
      $params
    );

    return array_map(function (array $res) {
      $galaxy = new GalaxyModel($res);

      // Récupération des systèmes solaires
      $solarSystemDao = new SolarSystemDao();
      $galaxy->solarSystems = $solarSystemDao->findAllByGalaxy($galaxy->id);

      return $galaxy;
    }, $result);
  }


  /**
   * Sélection d'une galaxie en base
   *
   * @param integer $id Identifiant de la galaxie
   * @return GalaxyModel Données de la galaxie
   */
  public function findOne(int $id): GalaxyModel {
    $params = [["id" => $id]];

    $result = $this->select(
      "SELECT id, name FROM galaxy WHERE id = :id",
      $params
    );

    $galaxy = new GalaxyModel($result[0] ?? []);

    // Récupération des systèmes solaires
    $solarSystemDao = new SolarSystemDao();
    $galaxy->solarSystems = $solarSystemDao->findAllByGalaxy($galaxy->id);

    return $galaxy;
  }


  /**
   * Ajout de galaxies dans la base
   *
   * @param integer $universeId Identifiant de l'univers
   * @param string[] $names Nom des galaxies
   * @return GalaxyModel[] Liste des galaxies
   */
  public function insertMultiples(int $universeId, array $names): array {
    $params = array_map(function (string $name) use ($universeId) {
      return [
        "name" => $name,
        "universe_id" => $universeId
      ];
    }, $names);

    $result = $this->insert(
      "INSERT INTO galaxy (universe_id, name) VALUES (:universe_id, :name)",
      $params
    );

    $galaxies = [];

    // Récupération des ID des galaxies
    foreach ($names as $index => $name) {
      $galaxy = new GalaxyModel();

      $galaxy->id = $result[$index] ?? 0;
      $galaxy->name = $name;

      array_push($galaxies, $galaxy);
    }

    return $galaxies;
  }
}
