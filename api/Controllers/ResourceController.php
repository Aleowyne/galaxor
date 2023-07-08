<?php

namespace App\Controllers;

use App\Dao\ResourceDao;
use App\Models\ResourceModel;
use App\Models\ItemModel;
use App\Exceptions;
use DateTime;

class ResourceController extends BaseController {
  private ResourceDao $resourceDao;
  private int $planetId;

  /**
   * Constructeur
   *
   * @param integer $planetId Identifiant de la planète
   */
  public function __construct(int $planetId) {
    $this->resourceDao = new ResourceDao();
    $this->planetId = $planetId;
  }


  /**
   * Récupération des ressources d'une planète
   *
   * @return ResourceModel[] Liste des ressources
   */
  public function getResourcesPlanet(): array {
    // Récupération des ressources de la planète
    $resources = $this->resourceDao->findAllByPlanet($this->planetId);

    // Calcul de la production et de la nouvelle quantité des ressources
    return $this->refreshResources($resources);
  }


  /**
   * Mise à jour des ressources d'une planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   */
  public function updateResourcesPlanet($resources): void {
    // Mise à jour des ressources sur la planète
    $this->resourceDao->updateMultiplesByPlanet($this->planetId, $resources);
  }


  /**
   * Calcul de la production et de la nouvelle quantité des ressources
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param string $upgradeItemId Identifiant de l'item upgradé
   * @return ResourceModel[] Liste des ressources
   */
  public function refreshResources(array $resources, string $upgradeItemId = ""): array {
    if ($resources) {
      // Calcul de la production de ressources par les structures sur la planète
      $this->calculateProduction($resources, $upgradeItemId);

      // Calcul de la nouvelle quantité des ressources sur la planète
      $this->calculateNewQuantity($resources);
    }

    return $resources;
  }


  /**
   * Contrôle de la disponibilité des ressources sur la planète pour upgrader un item
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param ItemModel $item Données de l'item
   * @return boolean Flag indiquant s'il y a suffisamment de ressources
   */
  public function checkAvailabilityResources(array $resources, ItemModel $item): bool {
    foreach ($item->costs as $cost) {
      $resource = $this->searchResource($resources, $cost->resourceId);

      // Si pas de quantité suffisante
      if ($resource->quantity < $cost->quantity) {
        return false;
      }

      $currentDate = new DateTime();

      // Calcul de la nouvelle quantité de la ressource sur la planète
      $resource->quantity -= $cost->quantity;
      $resource->lastTimeCalc = $currentDate->format(self::FORMAT_DATE);
    }

    return true;
  }


  /**
   * Ajout des coûts de construction d'une liste d'unités dans les ressources de la planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param array $units Données des unités
   * @return ResourceModel[] Liste des ressources
   */
  public function addCostsUnitsToResources(array $resources, array $units): array {
    $currentDate = new DateTime();

    foreach ($units as $unit) {
      foreach ($unit->costs as $cost) {
        $resource = $this->searchResource($resources, $cost->resourceId);
        $resource->quantity += $cost->quantity;
        $resource->lastTimeCalc = $currentDate->format(self::FORMAT_DATE);
      }
    }

    // Mise à jour des ressources sur la planète
    $this->updateResourcesPlanet($resources);

    return $resources;
  }


  /**
   * Ajout de ressources sur la planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param ResourceModel[] $addResources Ressources à déduire
   * @return ResourceModel[] Liste des ressources
   */
  public function addResources(array $resources, array $addResources): array {
    $currentDate = new DateTime();

    foreach ($addResources as $addResource) {
      $resource = $this->searchResource($resources, $addResource->id);
      $resource->quantity = $resource->quantity + $addResource->quantity;
      $resource->lastTimeCalc = $currentDate->format(self::FORMAT_DATE);
    }

    // Mise à jour des ressources
    $this->updateResourcesPlanet($resources);

    return $resources;
  }


  /**
   * Déduction de ressources sur la planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param ResourceModel[] $subtractResources Ressources à déduire
   * @return ResourceModel[] Liste des ressources
   */
  public function subtractResources(array $resources, array $subtractResources): array {
    $currentDate = new DateTime();

    foreach ($subtractResources as $subtractResource) {
      $resource = $this->searchResource($resources, $subtractResource->id);
      $resource->quantity = $resource->quantity - $subtractResource->quantity;
      $resource->lastTimeCalc = $currentDate->format(self::FORMAT_DATE);
    }

    // Mise à jour des ressources
    $this->updateResourcesPlanet($resources);

    return $resources;
  }


  /**
   * Calcul de la production de ressources par les structures sur une planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param string $upgradeItemId Identifiant de l'item upgradé
   * @return ResourceModel[] Liste des ressources
   */
  private function calculateProduction(array $resources, string $upgradeItemId = ""): array {
    $structureController = new StructureController($this->planetId);
    $structures = $structureController->getItemsPlanet();

    foreach ($structures as $structure) {
      /** @var StructureModel $structure **/
      foreach ($structure->formulasProd as $production) {
        $resource = $this->searchResource($resources, $production->resourceId);

        // Pas de mise à jour de la ressource "Energie", sauf si une structure augmentant l'énergie a été upgradée
        if (($production->resourceId === 3 && $structure->itemId !== $upgradeItemId)
          || $structure->level === 0
        ) {
          $resource->production = 0;
          continue;
        }

        $formula = self::RETURN_STMT . $production->formula . ";";
        $level = $structure->level;
        $bonus = $resource->bonus;

        // Mise à jour du nombre de ressources produites à la minute sur la planète
        $resource->production = (int) round(eval($formula));
      }
    }

    return $resources;
  }


  /**
   * Calcul de la nouvelle quantité des ressources sur la planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @return ResourceModel[] Liste des ressources
   */
  private function calculateNewQuantity(array $resources): array {
    // Calcul de la nouvelle quantité des ressources
    foreach ($resources as &$resource) {
      $currentDate = new DateTime();

      // Seules les ressources différentes de l'énergie sont dépendantes du temps
      if ($resource->id === 3) {
        $resource->quantity += $resource->production;

        if ($resource->production !== 0) {
          $resource->lastTimeCalc = $currentDate->format(self::FORMAT_DATE);
        }
      } else {
        $originDate = new DateTime($resource->lastTimeCalc);
        $minutes = (int) round(($currentDate->getTimestamp() - $originDate->getTimestamp()) / 60);

        $resource->quantity += ($resource->production * $minutes);

        if ($minutes !== 0) {
          $resource->lastTimeCalc = $currentDate->format(self::FORMAT_DATE);
        }
      }
    }

    return $resources;
  }


  /**
   * Récupération de la ressource de la planète
   *
   * @param ResourceModel[] $resources Liste des ressources
   * @param integer $resourceId Identifiant de la ressource
   * @return ResourceModel Données de la ressource
   */
  private function searchResource(array $resources, int $resourceId): ResourceModel {
    $resource = array_filter($resources, function (ResourceModel $resource) use ($resourceId) {
      return $resource->id === $resourceId;
    });

    return $resource ? current($resource) : new ResourceModel();
  }
}
