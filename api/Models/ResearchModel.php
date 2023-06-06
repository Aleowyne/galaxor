<?php

namespace App\Models;

class ResearchModel extends ItemModel {
  /**
   * Constructeur
   *
   * @param mixed[] $research Données de la recherche
   */
  public function __construct(array $research = []) {
    parent::__construct($research);
  }
}
