<?php

class PlanetController extends BaseController {
  private $planetModel = null;
  private $requestMethod = "";
  private $params = [];
  private $body = [];

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param array $params Paramètres de la requête
   * @param array $body Contenu de la requête
   */
  public function __construct(string $requestMethod, array $params, array $body) {
    $this->planetModel = new PlanetModel();
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
          $this->methodNotSupported();
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
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/solarsystem/:id/planets */
    if (preg_match("/\/api\/solarsystems\/[0-9]*\/planets$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getPlanetsBySolarSystem();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:id/ressources */
    if (preg_match("/\/api\/planets\/[0-9]*\/resources$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getResourcesPlanet();
          break;
        case "PUT":
          $this->updateResourcesPlanet();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/planets/:id/production */
    if (preg_match("/\/api\/planets\/[0-9]*\/production$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getProductionPlanet();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }


  /**
   * Récupération d'une planète
   */
  private function getPlanet(): void {
    $this->planetModel->setId($this->params[0] ?? 0);

    $result = $this->planetModel->findOne();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération des planètes
   */
  private function getPlanets(): void {
    $result = $this->planetModel->findAll();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération des planètes d'un système solaire
   */
  private function getPlanetsBySolarSystem(): void {
    $this->planetModel->setSolarSystemId($this->params[0] ?? 0);

    $result = $this->planetModel->findAllBySolarSystem();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération des ressources d'une planète
   */
  private function getResourcesPlanet(): void {
    $this->planetModel->setId($this->params[0] ?? 0);

    $result = $this->planetModel->findResourcesByPlanet();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération de la production en ressources par minute d'une planète
   */
  private function getProductionPlanet(): void {
    $this->planetModel->setId($this->params[0] ?? 0);

    $result = $this->planetModel->findOne();

    if (!$result) {
      $this->sendResponse("HTTP/1.1 404 Not Found");
      return;
    }

    // Calcul de la production de ressources sur la planète
    $resourcesProd = $this->calculateProduction();

    $productions = [];

    foreach ($resourcesProd as $resourceId => $prod) {
      array_push($productions, [
        "resource_id" => $resourceId,
        "production" => $prod
      ]);
    }

    if ($productions) {
      $this->sendResponse("HTTP/1.1 200 OK", $productions);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Création de planètes pour plusieurs systèmes solaires
   */
  public function createPlanets(): array {
    foreach ($this->params as $solarSystemId) {
      // Génération du nom des planètes
      $names = $this->randomName(rand(4, 10));

      foreach ($names as $name) {
        $this->planetModel->addName($name, $solarSystemId);
      }
    }

    return $this->planetModel->insertMultiples();
  }


  /**
   * Assignation d'un utilisateur à une planète
   */
  private function assignUserPlanet(): void {
    $this->planetModel->setId($this->params[0] ?? 0);
    $this->planetModel->setUserId($this->body["user_id"] ?? "");

    // Données de l'utilisateur non valide
    if (!$this->checkUserId()) {
      $this->invalidBody();
      return;
    }

    $result = $this->planetModel->updateOne();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK");
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Mise à jour des ressources d'une planète
   * 
   * @param boolean $updateEnergy Flag indiquant si le nombre de ressources "Energie" doit être mis à jour
   */
  private function updateResourcesPlanet(bool $updateEnergy = false): void {
    $this->planetModel->setId($this->params[0] ?? 0);

    // Récupération des ressources de la planète lors de la dernière mise à jour
    $resourcesPlanet = $this->planetModel->findResourcesByPlanet();

    if (!$resourcesPlanet) {
      $this->sendResponse("HTTP/1.1 404 Not Found");
      return;
    }

    // Calcul de la production de ressources sur la planète
    $resourcesProd = $this->calculateProduction($updateEnergy);

    // Calcul de la nouvelle quantité des ressources
    foreach ($resourcesPlanet as $resource) {
      // Les ressources différentes de l'énergie sont dépendantes du temps
      if ($resource["resource_id"] === 3) {
        $newQuantity = $resource["quantity"] + $resourcesProd[$resource["resource_id"]];
      } else {
        $originDate = new DateTime($resource["last_time_calc"]);
        $targetDate = new DateTime();
        $minutes = (int) round(($targetDate->getTimestamp() - $originDate->getTimestamp()) / 60);

        $newQuantity = $resource["quantity"] + ($resourcesProd[$resource["resource_id"]] * $minutes);
      }

      $this->planetModel->addResources($resource["resource_id"], $newQuantity);
    }

    // Mise à jour des ressources sur la planète
    $result = $this->planetModel->updateResources();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $this->planetModel->getResources());
    } else {
      $this->sendResponse("HTTP/1.1 204 No Content");
    }
  }


  /**
   * Calcul de la production de ressource sur une planète
   *
   * @param boolean $updateEnergy Flag indiquant si le nombre de ressources "Energie" doit être mis à jour
   * @return array
   */
  private function calculateProduction(bool $updateEnergy = false): array {
    $resourcesProd = array_fill(1, 3, 0);

    // Récupération des formules de production
    $formulasProd = $this->planetModel->findFormulasProdByPlanet();

    foreach ($formulasProd as $formulaProd) {
      // Pas de mise à jour de la ressource "Energie"
      if (!$updateEnergy && $formulaProd['resource_id'] === 3) {
        continue;
      }

      $formula = "return " . $formulaProd['production'] . ";";
      $level = $formulaProd['level'];
      $bonus = $formulaProd['bonus'];
      $resourceId = $formulaProd['resource_id'];

      $resourcesProd[$resourceId] += (int) round(eval($formula));
    }

    return $resourcesProd;
  }


  /**
   * Contrôle des données de l'utilisateur 
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserId(): bool {
    return $this->planetModel->getUserId();
  }
}
