<?php

class PlanetController extends BaseController {
  private $planetDao = null;
  private $structureDao = null;
  private $resourceDao = null;
  private $planet = null;
  private $requestMethod = "";
  private $params = [];
  private $body = [];

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param mixed[] $params Paramètres de la requête
   * @param mixed[] $body Contenu de la requête
   */
  public function __construct(string $requestMethod, array $params, array $body) {
    $this->planetDao = new PlanetDao();
    $this->structureDao = new StructureDao();
    $this->resourceDao = new ResourceDao();
    $this->requestMethod = $requestMethod;
    $this->params = $params;
    $this->body = $body;
  }


  /**
   * Traitement de la requête
   *
   * @param string $uri URI
   */
  public function processRequest(string $uri): void {
    /* Endpoint /api/planets/:id */
    if (preg_match("/\/api\/planets\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getPlanet();
          break;
        case "PUT":
          $this->assignUserPlanet();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets */
    if (preg_match("/\/api\/planets$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getPlanets();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:id/ressources */
    if (preg_match("/\/api\/planets\/[0-9]*\/resources$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $this->updateResourcesPlanet();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:planet_id/structures/:structure_id */
    if (preg_match("/\/api\/planets\/[0-9]*\/structures\/[A-Z_]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "PUT":
          $this->updateStructurePlanet();
          break;
        default:
          $this->sendMethodNotSupported();
      }
      return;
    }

    $this->sendErrorResponse("URL non valide");
  }


  /**
   * Récupération d'une planète
   */
  private function getPlanet(): void {
    $planetId = (int) ($this->params[0] ?? 0);

    $isPlanetExist = $this->refreshPlanet($planetId);

    if ($isPlanetExist) {
      $this->sendSuccessResponse($this->planet->toArray());
    } else {
      $this->sendErrorResponse("Planète non trouvée");
    }
  }


  /**
   * Récupération des planètes
   */
  private function getPlanets(): void {
    $planets = $this->planetDao->findAll();

    $arrayPlanets = [
      "planets" => array_map(function (PlanetModel $planet) {
        return $planet->toSimpleArray();
      }, $planets)
    ];

    $this->sendSuccessResponse($arrayPlanets);
  }


  /**
   * Récupération des données d'une planète 
   * 
   * @param integer $planetId Identifiant de la planète
   * @param string $upStructureId Identifiant de la structure upgradée
   * @return boolean Flag indiquant si la planète existe
   */
  private function refreshPlanet(int $planetId, string $upStructureId = ""): bool {
    $this->planet = $this->planetDao->findOne($planetId);

    if (!$this->planet->id) {
      return false;
    }

    // Récupération des structures de la planète
    $this->planet->structures = $this->structureDao->findAllByPlanet($this->planet->id);

    // Calcul de la production de ressources par les structures sur la planète
    $this->calculateProduction($upStructureId);

    // Evaluation des formules des structures d'une planète
    $this->evaluateFormulas();

    return true;
  }


  /**
   * Création de planètes pour plusieurs systèmes solaires
   * 
   * @return PlanetModel[] Liste des planètes
   */
  public function createPlanets(): array {
    $solarSystemId = (int) ($this->params[0] ?? 0);

    // Génération du nom des planètes
    $names = $this->randomName(rand(4, 10));

    return $this->planetDao->insertMultiples($solarSystemId, $names);
  }


  /**
   * Assignation d'un utilisateur à une planète
   */
  private function assignUserPlanet(): void {
    // Données de l'utilisateur non valide
    if (!$this->checkUserId()) {
      $this->sendInvalidBody();
      return;
    }

    $planetId = (int) ($this->params[0] ?? 0);
    $userId = $this->body["user_id"];

    $result = $this->planetDao->updateOne($planetId, $userId);

    if ($result) {
      $this->sendSuccessResponse();
    } else {
      $this->sendErrorResponse();
    }
  }


  /**
   * Mise à jour des ressources d'une planète
   */
  private function updateResourcesPlanet(): void {
    $planetId = (int) ($this->params[0] ?? 0);

    // Récupération des données de la planète
    $isPlanetExist = $this->refreshPlanet($planetId);

    if (!$isPlanetExist) {
      $this->sendErrorResponse("Planète non trouvée");
      return;
    }

    // Calcul de la nouvelle quantité des ressources sur la planète
    $this->calculateNewQuantity();

    // Mise à jour des ressources sur la planète
    $result = $this->resourceDao->updateMultiples($planetId, $this->planet->resources);

    if ($result) {
      $this->sendSuccessResponse($this->planet->toResourcesArray());
    } else {
      $this->sendNoContentResponse();
    }
  }


  /**
   * Mise à jour d'une structure d'une planète (prise d'un niveau)
   */
  private function updateStructurePlanet(): void {
    $planetId = (int) ($this->params[0] ?? 0);
    $structureId = $this->params[1] ?? "";

    // Récupération des données de la planète
    $isPlanetExist = $this->refreshPlanet($planetId);

    if (!$isPlanetExist) {
      $this->sendErrorResponse("Planète non trouvée");
      return;
    }

    // Calcul de la nouvelle quantité des ressources sur la planète
    $this->calculateNewQuantity();

    // Mise à jour des ressources sur la planète avant upgrade de la structure
    $this->resourceDao->updateMultiples($planetId, $this->planet->resources);

    // Récupération de la structure sur la planète
    $structure = $this->planet->getStructure($structureId);

    if (!$structure->id) {
      $this->sendErrorResponse("Structure non trouvée");
      return;
    }

    $currentDate = new DateTime();

    if (!$structure->upgradeInProgress) {
      // Contrôle de la disponibilité des ressources pour upgrade
      if (!$this->checkAvailabilityResources($structure)) {
        $this->sendErrorResponse("Ressources insuffisantes");
        return;
      }

      // Détermination de la date de fin de l'upgrade
      $buildTime = new DateInterval("PT" . $structure->buildTime . "S");
      $structure->timeEndUpgrade = $currentDate->add($buildTime)->format("Y-m-d H:i:s");
      $structure->upgradeInProgress = 1;

      // Mise à jour de la structure
      $result = $this->structureDao->updateOne($planetId, $structure);
    } else {
      $endBuildDate = new DateTime($structure->timeEndUpgrade);

      // Upgrade terminé
      if ($currentDate >= $endBuildDate) {
        $structure->level++;
        $structure->upgradeInProgress = 0;
      }

      // Mise à jour de la structure
      $result = $this->structureDao->updateOne($planetId, $structure);

      // Récupération des données de la planète
      $this->refreshPlanet($planetId, $structure->id);

      // Calcul de la nouvelle quantité des ressources sur la planète
      $this->calculateNewQuantity();
    }

    // Mise à jour des ressources sur la planète après upgrade de la structure
    $this->resourceDao->updateMultiples($planetId, $this->planet->resources);

    if ($result) {
      $this->sendSuccessResponse($this->planet->toArray());
    } else {
      $this->sendNoContentResponse();
    }
  }


  /**
   * Calcul de la production de ressources par les structures sur une planète
   *
   * @param string $upStructureId Identifiant de la structure upgradée
   */
  private function calculateProduction(string $upStructureId = ""): void {
    foreach ($this->planet->structures as $structure) {
      /** @var StructureModel $structure **/
      foreach ($structure->formulasProd as $production) {
        // Pas de mise à jour de la ressource "Energie"
        if (($production->resourceId === 3 && $structure->id !== $upStructureId)
          || $structure->level === 0
        ) {
          continue;
        }

        $resource = $this->planet->getResource($production->resourceId);

        $formula = "return " . $production->formula . ";";
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
    foreach ($this->planet->resources as &$resource) {
      $currentDate = new DateTime();

      // Seule les ressources différentes de l'énergie sont dépendantes du temps
      if ($resource->id === 3) {
        $resource->quantity += $resource->production;

        if ($resource->production !== 0) {
          $resource->lastTimeCalc = $currentDate->format("Y-m-d H:i:s");
        }
      } else {
        $originDate = new DateTime($resource->lastTimeCalc);
        $minutes = (int) round(($currentDate->getTimestamp() - $originDate->getTimestamp()) / 60);

        $resource->quantity += ($resource->production * $minutes);

        if ($minutes !== 0) {
          $resource->lastTimeCalc = $currentDate->format("Y-m-d H:i:s");
        }
      }
    }
  }

  /**
   * Evaluation des formules des items d'une planètes (durée de construction, coût, pré-requis)
   */
  private function evaluateFormulas(): void {
    $itemDao = new ItemDao();
    $allItems = $itemDao->findAllByPlanet($this->planet->id);
    $formulasCost = $itemDao->findCosts();
    $prerequisitesItems = $itemDao->findPrerequisites();

    // Récupération des variables pour les formules
    foreach ($allItems as $item) {
      $variable = strtolower($item->id);
      $$variable = $item->level;
    }

    $itemTypes = ["structures", "researches", "units"];

    foreach ($itemTypes as $itemType) {
      foreach ($this->planet->{$itemType} as &$item) {
        /** @var ItemModel $item **/

        // Calcul de la durée de construction
        $formula = "return " . $item->buildTime . ";";
        $level = $item->level;

        $item->buildTime = (int) round(eval($formula));

        // Récupération du coût de l'item pour prendre un niveau
        $item->costs = array_values(
          array_filter($formulasCost, function (CostModel $formulaCost) use ($item) {
            return ($formulaCost->itemId === $item->id);
          })
        );

        // Calcul des coûts
        foreach ($item->costs as &$cost) {
          $formula = "return " . $cost->quantity . ";";
          $cost->quantity = (int) round(eval($formula));
        }

        if ($item->level === 0) {
          // Récupération des pré-requis pour construire l'item
          $prerequisites = array_values(
            array_filter($prerequisitesItems, function (PrerequisiteModel $prerequisite) use ($item) {
              return $prerequisite->itemId === $item->id;
            })
          );

          // Récupération des pré-requis non validés
          foreach ($prerequisites as $prerequisite) {
            $variable = strtolower($prerequisite->requiredItemId);

            if (isset($$variable) && $$variable < $prerequisite->level) {
              array_push($item->prerequisites, $prerequisite);
            }
          }
        }
      }
    }
  }


  /**
   * Contrôle de la disponibilité des ressources sur la planète pour upgrade une structure
   *
   * @param StructureModel $structure Données de la structure
   * @return boolean Flag indiquant s'il y a suffisamment de ressources
   */
  private function checkAvailabilityResources(StructureModel $structure): bool {
    foreach ($structure->costs as $cost) {
      $resource = $this->planet->getResource($cost->resourceId);

      // Si pas de quantité suffisante
      if ($resource->quantity < $cost->quantity) {
        return false;
      }

      // Calcul de la nouvelle quantité de la ressource sur la planète
      $resource->quantity -= $cost->quantity;
    }

    return true;
  }


  /**
   * Contrôle des données de l'utilisateur 
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserId(): bool {
    $userId = $this->body["user_id"] ?? "";

    return (bool) $userId;
  }
}
