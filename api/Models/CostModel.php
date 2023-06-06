<?php

namespace App\Models;

class CostModel {
  public string $itemId;
  public int $resourceId;
  public string $resourceName;
  public string|int $quantity;

  /**
   * Constructeur
   *
   * @param mixed[] $item Données du coût de l'item
   */
  public function __construct(array $cost = []) {
    $this->itemId = $cost["item_id"] ?? "";
    $this->resourceId = $cost["resource_id"] ?? 0;
    $this->resourceName = $cost["resource_name"] ?? "";
    $this->quantity = $cost["quantity"] ?? "";
  }

  /**
   * Transformation des données du coût de l'item sous forme de tableau
   *
   * @return mixed[] Données du coût de l'item
   */
  public function toArray(): array {
    return [
      "resource_id" => $this->resourceId,
      "resource_name" => $this->resourceName,
      "quantity" => $this->quantity
    ];
  }
}
