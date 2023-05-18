<?php

class ProductionModel {
  public int $resourceId;
  public string $formula;

  /**
   * Constructeur
   *
   * @param mixed[] $production Données de production
   */
  public function __construct(array $production = []) {
    $this->resourceId = $production["resource_id"] ?? 0;
    $this->formula = $production["production"] ?? "";
  }
}
