<?php

class PlanetModel extends Database {
  private $id = 0;
  private $solarSystemId = 0;
  private $userId = 0;
  private $resources = [];
  private $listNames = [];

  public function setId(string $id): void {
    $this->id = (int) $id;
  }

  public function setSolarSystemId(string $id): void {
    $this->solarSystemId = (int) $id;
  }

  public function setUserId(string $id): void {
    $this->userId = (int) $id;
  }

  public function addName(string $name, int $solarSystemId): void {
    $solarSystem = [
      "name" => $name,
      "solar_system_id" => $solarSystemId,
      "position" => rand(1, 10)
    ];

    array_push($this->listNames, $solarSystem);
  }

  public function addResources(int $resourceId, int $quantity): void {
    $resource = [
      "planet_id" => $this->id,
      "resource_id" => $resourceId,
      "quantity" => $quantity
    ];

    array_push($this->resources, $resource);
  }

  public function getId(): int {
    return $this->id;
  }

  public function getSolarSystemId(): int {
    return $this->solarSystemId;
  }

  public function getUserId(): int {
    return $this->userId;
  }

  public function getNames(): array {
    return $this->listNames;
  }

  public function getResources(): array {
    return $this->resources;
  }


  /**
   * Sélection de toutes les planètes en base
   *
   * @return array Liste de toutes les planètes
   */
  public function findAll(): array {
    return $this->select(
      "SELECT p.id AS planet_id, p.name AS planet_name, s.name AS solar_system_name,
              g.name AS galaxy_name, u.name AS universe_name
        FROM planet AS p
        INNER JOIN solar_system AS s
          ON p.solar_system_id = s.id
        INNER JOIN galaxy AS g
          ON s.galaxy_id = g.id
        INNER JOIN universe AS u
          ON g.universe_id = u.id
        ORDER BY p.id ASC"
    );
  }


  /**
   * Sélection de toutes les planètes d'un système solaire en base
   *
   * @return array Liste des planètes
   */
  public function findAllBySolarSystem(): array {
    $params = [["solar_system_id" => $this->solarSystemId]];

    return $this->select(
      "SELECT id, name FROM planet WHERE solar_system_id = :solar_system_id",
      $params
    );
  }


  /**
   * Sélection d'une planète en base
   *
   * @return array Données de la planète
   */
  public function findOne(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT p.id, p.name AS planet_name, p.position, p.user_id, s.size, b.resource_id,
              r.name AS resource_name, b.bonus
        FROM planet AS p
        NATURAL JOIN planet_size AS s
        NATURAL JOIN position_bonus AS b
        INNER JOIN resource AS r
          ON b.resource_id = r.id
        WHERE p.id = :id
        ORDER BY b.resource_id",
      $params
    );
  }


  /**
   * Sélection des ressources d'une planète en base
   *
   * @return array Ressources de la planète
   */
  public function findResourcesByPlanet(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT pr.planet_id, p.name AS planet_name, pr.resource_id, r.name AS resource_name, 
              pr.quantity, pr.last_time_calc
        FROM planet_resource AS pr
        INNER JOIN planet AS p
          ON pr.planet_id = p.id
        INNER JOIN resource AS r
          ON pr.resource_id = r.id
        WHERE pr.planet_id = :id
        ORDER BY pr.resource_id",
      $params
    );
  }


  /**
   * Sélection des formules de production des ressources avec le niveau des structures d'une planète en base
   *
   * @return array Formules de production et niveau des structures de la planète
   */
  public function findFormulasProdByPlanet(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT ip.item_id, ip.resource_id, ip.production, pi.level, b.bonus
        FROM item_production AS ip
        NATURAL JOIN planet_item AS pi
        INNER JOIN planet AS p
          ON pi.planet_id = p.id
        INNER JOIN planet_size AS s
          ON p.position = s.position
        INNER JOIN position_bonus AS b
          ON s.position = b.position
            AND ip.resource_id = b.resource_id
        WHERE pi.planet_id = :id
        ORDER BY ip.resource_id",
      $params
    );
  }


  /**
   * Ajout de planètes dans la base
   *
   * @return array Liste des ID des planètes
   */
  public function insertMultiples(): array {
    return $this->insert(
      "INSERT INTO planet (solar_system_id, name, position) 
        VALUES (:solar_system_id, :name, :position)",
      $this->listNames
    );
  }


  /**
   * Mise à jour d'une planète dans la base
   *
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateOne(): bool {
    $params = [[
      "id" => $this->id,
      "user_id" => $this->userId
    ]];

    return $this->update(
      "UPDATE planet SET user_id = :user_id WHERE id = :id",
      $params
    );
  }


  /**
   * Mise à jour des ressources d'une planète dans la base
   *
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function updateResources(): bool {
    return $this->update(
      "UPDATE planet_resource 
        SET quantity = :quantity,
            last_time_calc = now()
        WHERE planet_id = :planet_id
          AND resource_id = :resource_id",
      $this->resources
    );
  }
}
