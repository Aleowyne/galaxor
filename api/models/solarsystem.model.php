<?php

class SolarSystemModel extends Database {
  private $id = 0;
  private $galaxyId = 0;
  private $listNames = [];

  public function setId(string $id): void {
    $this->id = (int) $id;
  }

  public function setGalaxyId(string $id): void {
    $this->galaxyId = (int) $id;
  }

  public function addName(string $name, int $galaxyId): void {
    $solarSystem = [
      "name" => $name,
      "galaxy_id" => $galaxyId
    ];

    array_push($this->listNames, $solarSystem);
  }

  public function getId(): int {
    return $this->id;
  }

  public function getGalaxyId(): int {
    return $this->galaxyId;
  }

  public function getNames(): array {
    return $this->listNames;
  }


  /**
   * Sélection de tous les systèmes solaires d'une galaxie en base
   *
   * @return array Liste des systèmes solaires
   */
  public function findAllByGalaxy(): array {
    $params = [["galaxy_id" => $this->galaxyId]];

    return $this->select(
      "SELECT id, name FROM solar_system WHERE galaxy_id = :galaxy_id",
      $params
    );
  }


  /**
   * Sélection d'une système solaire en base
   *
   * @return array Données du système solaire
   */
  public function findOne(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT id, name FROM solar_system WHERE id = :id",
      $params
    );
  }


  /**
   * Ajout de systèmes solaires dans la base
   *
   * @return array Liste des ID des systèmes solaires
   */
  public function insertMultiples(): array {
    return $this->insert(
      "INSERT INTO solar_system (galaxy_id, name) VALUES (:galaxy_id, :name)",
      $this->listNames
    );
  }
}
