<?php

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


  /**
   * Transformation des données de la structure sous forme de tableau
   *
   * @return mixed[] Données de la structure
   */
  public function toArray(): array {
    $array = parent::toArray();

    return $array;
  }
}
