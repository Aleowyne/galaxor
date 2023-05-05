<?php

class GalaxyModel extends Database {
  private $id = 0;
  private $universeId = 0;
  private $listNames = [];

  public function setId(string $id): void {
    $this->id = (int) $id;
  }

  public function setUniverseId(string $id): void {
    $this->universeId = (int) $id;
  }

  public function addName(string $name): void {
    $galaxy = [
      "name" => $name,
      "universe_id" => $this->universeId
    ];

    array_push($this->listNames, $galaxy);
  }

  public function getId(): int {
    return $this->id;
  }

  public function getUniverseId(): int {
    return $this->universeId;
  }

  public function getNames(): array {
    return $this->listNames;
  }


  /**
   * Sélection de toutes les galaxies d'un univers en base
   *
   * @return array Liste des galaxies
   */
  public function findAllByUniverse(): array {
    $params = [["universe_id" => $this->universeId]];

    return $this->select(
      "SELECT id, name FROM galaxy WHERE universe_id = :universe_id",
      $params
    );
  }


  /**
   * Sélection d'une galaxie en base
   *
   * @return array Données de la galaxie
   */
  public function findOne(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT id, name FROM galaxy WHERE id = :id",
      $params
    );
  }


  /**
   * Ajout de galaxies dans la base
   *
   * @return array Liste des ID des galaxies
   */
  public function insertMultiples(): array {
    return $this->insert(
      "INSERT INTO galaxy (universe_id, name) VALUES (:universe_id, :name)",
      $this->listNames
    );
  }
}
