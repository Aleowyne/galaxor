<?php

class UniverseModel extends Database {
  private $id = 0;
  private $name = "";

  public function setId(string $id): void {
    $this->id = (int) $id;
  }

  public function setName(string $name): void {
    $this->name = $name;
  }

  public function getId(): int {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }


  /**
   * Sélection de tous les univers en base
   *
   * @return array Liste de tous les univers
   */
  public function findAll(): array {
    return $this->select(
      "SELECT id, name FROM universe ORDER BY id ASC"
    );
  }


  /**
   * Sélection d'un univers en base
   *
   * @return array Données d'un univers
   */
  public function findOne(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT id, name FROM universe WHERE id = :id",
      $params
    );
  }


  /**
   * Ajout d'un univers dans la base
   */
  public function insertOne(): void {
    $params = [["name" => $this->name]];

    $ids = $this->insert(
      "INSERT INTO universe (name) VALUES (:name)",
      $params
    );

    $this->setId($ids[0] ?? 0);
  }
}
