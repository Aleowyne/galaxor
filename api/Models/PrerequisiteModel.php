<?php

namespace App\Models;

class PrerequisiteModel {
  public string $itemId;
  public string $requiredItemId;
  public string $requiredItemName;
  public int $level;

  /**
   * Constructeur
   *
   * @param mixed[] $item Données du pré-requis de l'item
   */
  public function __construct(array $cost = []) {
    $this->itemId = $cost["item_id"] ?? "";
    $this->requiredItemId = $cost["required_item_id"] ?? "";
    $this->requiredItemName = $cost["required_item_name"] ?? "";
    $this->level = $cost["level"] ?? 0;
  }

  /**
   * Transformation des données du pré-requis de l'item sous forme de tableau
   *
   * @return mixed[] Données du pré-requis de l'item
   */
  public function toArray(): array {
    return [
      "required_item_id" => $this->requiredItemId,
      "required_item_name" => $this->requiredItemName,
      "level" => $this->level
    ];
  }
}
