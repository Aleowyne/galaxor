<?php

namespace App\Models;

class ResourceModel {
  public int $id;
  public string $name;
  public int $bonus;
  public int $quantity;
  public string $lastTimeCalc;
  public int $production;

  /**
   * Constructeur
   *
   * @param mixed[] $resource Données de la ressource
   */
  public function __construct(array $resource = []) {
    $this->id = $resource["resource_id"] ?? 0;
    $this->name = $resource["resource_name"] ?? "";
    $this->bonus = $resource["bonus"] ?? 0;
    $this->quantity = $resource["quantity"] ?? 0;
    $this->lastTimeCalc = $resource["last_time_calc"] ?? "";
    $this->production = 0;
  }


  /**
   * Transformation des données de la ressource sous forme de tableau
   *
   * @return mixed[] Données de la ressource
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "bonus" => $this->bonus,
      "quantity" => $this->quantity,
      "last_time_calc" => $this->lastTimeCalc,
      "production" => $this->production
    ];
  }


  /**
   * Transformation des données de la ressource sous forme de tableau
   * pour les combats
   *
   * @return mixed[] Données de la ressource
   */
  public function toArrayForFight(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "quantity" => $this->quantity
    ];
  }
}
