<?php

class SolarSystemDao extends Database {
  /**
   * Sélection de tous les systèmes solaires d'une galaxie en base
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @return SolarSystemModel[] Liste des systèmes solaires
   */
  public function findAllByGalaxy(int $galaxyId): array {
    $params = [["galaxy_id" => $galaxyId]];

    $result =  $this->select(
      "SELECT id, name FROM solar_system WHERE galaxy_id = :galaxy_id",
      $params
    );

    return array_map(function (array $res) {
      $solarSystem = new SolarSystemModel($res);

      // Récupération des planètes
      $planetDao = new PlanetDao();
      $solarSystem->planets = $planetDao->findAllBySolarSystem($solarSystem->id);

      return $solarSystem;
    }, $result);
  }


  /**
   * Sélection d'une système solaire en base
   *
   * @param integer $id Identifiant du système solaire
   * @return SolarSystemModel Données du système solaire
   */
  public function findOne(int $id): SolarSystemModel {
    $params = [["id" => $id]];

    $result = $this->select(
      "SELECT id, name FROM solar_system WHERE id = :id",
      $params
    );

    $solarSystem = new SolarSystemModel($result[0] ?? []);

    // Récupération des planètes
    $planetDao = new PlanetDao();
    $solarSystem->planets = $planetDao->findAllBySolarSystem($solarSystem->id);

    return $solarSystem;
  }


  /**
   * Ajout de systèmes solaires dans la base
   *
   * @param integer $galaxyId Identifiant de la galaxie
   * @param string[] $names Nom des systèmes solaires
   * @return SolarSystemModel[] Liste des systèmes solaires
   */
  public function insertMultiples(int $galaxyId, array $names): array {
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
      $solarSystem = new SolarSystemModel();

      $solarSystem->id = $result[$index] ?? 0;
      $solarSystem->name = $name;

      array_push($solarSystems, $solarSystem);
    }

    return $solarSystems;
  }
}
