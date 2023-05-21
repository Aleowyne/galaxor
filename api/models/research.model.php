<?php

class ResearchModel extends ItemModel {
  /**
   * Constructeur
   *
   * @param mixed[] $research Données de la recherche
   */
  public function __construct(array $research = []) {
    parent::__construct($research);
  }


  /**
   * Transformation des données de la recherche sous forme de tableau
   *
   * @return mixed[] Données de la recherche
   */
  public function toArray(): array {
    $array = parent::toArray();

    return $array;
  }
}
