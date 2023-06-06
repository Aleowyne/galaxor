<?php

namespace App\Controllers;

use App\Dao\ResourceDao;
use App\Models\ResourceModel;
use App\Models\ItemModel;
use App\Exceptions;
use DateTime;

class ResourceController extends BaseController {
  private $resourceDao = null;
  private $planetId = 0;
  /** @var ResourceModel[] $resources **/
  private $resources = [];

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
   * @param ResourceModel[] Liste des ressources
   */
  public function getResourcesPlanet(): array {
    // Récupération des ressources de la planète
    $this->resources = $this->resourceDao->findAllByPlanet($this->planetId);

    // Calcul de la production et de la nouvelle quantité des ressources
    $this->refreshResources();

    return $this->resources;
  }


  /**
   * Mise à jour des ressources d'une planète
   */
  public function updateResourcesPlanet(): void {
    // Mise à jour des ressources sur la planète
    $isUpdate = $this->resourceDao->updateMultiples($this->planetId, $this->resources);

    if (!$isUpdate) {
      throw new Exceptions\InternalErrorException("Upgrade a échoué");
    }
  }


  /**
   * Calcul de la production et de la nouvelle quantité des ressources
   *
   * @param string $upgradeItemId Identifiant de l'item upgradé
   * @param ResourceModel[] Liste des ressources
   */
  public function refreshResources(string $upgradeItemId = ""): array {
    if ($this->resources) {
      // Calcul de la production de ressources par les structures sur la planète
      $this->calculateProduction($upgradeItemId);

      // Calcul de la nouvelle quantité des ressources sur la planète
      $this->calculateNewQuantity();
    }

    return $this->resources;
  }


  /**
   * Contrôle de la disponibilité des ressources sur la planète pour upgrader un item
   *
   * @param ItemModel $item Données de l'item
   * @return boolean Flag indiquant s'il y a suffisamment de ressources
   */
  public function checkAvailabilityResources(ItemModel $item): bool {
    foreach ($item->costs as $cost) {
      $resource = $this->searchResource($cost->resourceId);

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
   * Calcul de la production de ressources par les structures sur une planète
   *
   * @param string $upgradeItemId Identifiant de l'item upgradé
   */
  private function calculateProduction(string $upgradeItemId = ""): void {
    $structureController = new StructureController($this->planetId);
    $structures = $structureController->getItemsPlanet();

    foreach ($structures as $structure) {
      /** @var StructureModel $structure **/
      foreach ($structure->formulasProd as $production) {
        $resource = $this->searchResource($production->resourceId);

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
  }


  /**
   * Calcul de la nouvelle quantité des ressources sur la planète
   */
  private function calculateNewQuantity(): void {
    // Calcul de la nouvelle quantité des ressources
    foreach ($this->resources as &$resource) {
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
  }


  /**
   * Récupération de la ressource de la planète
   *
   * @param integer $resourceId Identifiant de la ressource
   * @return ResourceModel Données de la ressource
   */
  private function searchResource(int $resourceId): ResourceModel {
    $resource = array_filter($this->resources, function (ResourceModel $resource) use ($resourceId) {
      return $resource->id === $resourceId;
    });

    return $resource ? current($resource) : new ResourceModel();
  }
}
