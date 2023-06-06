<?php

namespace App\Models;

class StructureModel extends ItemModel {
  /** @var ProductionModel[] $formulasProd **/
  public array $formulasProd;

  /**
   * Constructeur
   *
   * @param mixed[] $structure Données de la structure
   */
  public function __construct(array $structure = []) {
    parent::__construct($structure);
    $this->formulasProd = [];
  }
}
