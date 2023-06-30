<?php

namespace App\Models;

class StructureModel extends ItemModel {
  /** @var ProductionModel[] $formulasProd **/
  public array $formulasProd;
  public int $quantity;

  /**
   * Constructeur
   *
   * @param mixed[] $structure Données de la structure
   */
  public function __construct(array $structure = []) {
    parent::__construct($structure);
    $this->formulasProd = [];
    $this->quantity = $structure["quantity"] ?? 0;
  }


  /**
   * Transformation des données de la structure sous forme de tableau
   * pour les combats
   *
   * @return mixed[] Données de la structure
   */
  public function toArrayForFight(): array {
    return [
      "item_id" => $this->itemId,
      "name" => $this->name,
      "quantity" => $this->quantity
    ];
  }
}
