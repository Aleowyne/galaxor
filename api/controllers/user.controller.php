<?php

class UserController extends BaseController {
  private $userModel = null;
  private string $requestMethod = "";
  private array $params = [];
  private array $body = [];

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param array $params Paramètres de la requête
   * @param array $body Contenu de la requête
   */
  public function __construct(string $requestMethod, array $params, array $body) {
    $this->userModel = new UserModel();
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
    /* Endpoint /api/users/:id */
    if (preg_match("/\/api\/users\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getUser();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    /* Endpoint /api/users */
    if (preg_match("/\/api\/users$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getUsers();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    // Endpoint /api/users/register
    if (preg_match("/\/api\/users\/register$/", $uri)) {
      switch ($this->requestMethod) {
        case "POST":
          $this->register();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    // Endpoint /api/users/login
    if (preg_match("/\/api\/users\/login$/", $uri)) {
      switch ($this->requestMethod) {
        case "POST":
          $this->login();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    // Endpoint /api/users/logout
    if (preg_match("/\/api\/users\/logout$/", $uri)) {
      switch ($this->requestMethod) {
        case "POST":
          $this->logout();
          break;
        default:
          $this->methodNotSupported();
      }
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }


  /**
   * Récupération d'un utilisateur
   */
  private function getUser(): void {
    $this->userModel->setId($this->params[0] ?? 0);

    $result = $this->userModel->findOneById();

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Récupération de plusieurs utilisateurs
   */
  private function getUsers(): void {
    $result = $this->userModel->findAll();

    $this->sendResponse("HTTP/1.1 200 OK", $result);
  }


  /**
   * Enregistrer un utilisateur
   */
  private function register(): void {
    $this->userModel->setMailAddress($this->body["mail_address"] ?? "");
    $this->userModel->setName($this->body["name"] ?? "");
    $this->userModel->setPassword($this->body["password"] ?? "");

    // Données de l'utilisateur non valides
    if (!$this->checkUserRegister()) {
      $this->invalidBody();
      return;
    }

    // Utilisateur déjà enregistré
    if ($this->userModel->findOneByAddress() || $this->userModel->findOneByName()) {
      $this->invalidBody();
      return;
    }

    $this->userModel->insertOne();

    if ($this->userModel->getId() != 0) {
      $user = ["id" => $this->userModel->getId()];

      $this->sendResponse("HTTP/1.1 201 Created", $user);
    } else {
      $this->sendResponse("HTTP/1.1 500 Internal Server Error");
    }
  }


  /**
   * Connexion d'un utilisateur
   */
  private function login(): void {
    // Utilisateur déjà connecté
    if (isset($_SESSION["id"]) && isset($_SESSION["name"])) {
      $user = [
        "id" => $_SESSION["id"],
        "name" => $_SESSION["name"]
      ];

      $this->sendResponse("HTTP/1.1 200 OK", $user);
      return;
    }

    $this->userModel->setMailAddress($this->body["mail_address"] ?? "");
    $this->userModel->setPassword($this->body["password"] ?? "");

    // Données de l'utilisateur non valide
    if (!$this->checkUserLogin()) {
      $this->invalidBody();
      return;
    }

    $result = $this->userModel->login();

    if ($result) {
      $user = [
        "id" => $this->userModel->getId(),
        "name" => $this->userModel->getName()
      ];

      $_SESSION = [
        "id" => $this->userModel->getId(),
        "name" => $this->userModel->getName()
      ];

      $this->sendResponse("HTTP/1.1 200 OK", $user);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }


  /**
   * Déconnexion d'un utilisateur
   */
  private function logout(): void {
    $_SESSION = array();
    session_destroy();

    $this->sendResponse("HTTP/1.1 200 OK");
  }


  /**
   * Contrôle des données de l'utilisateur à l'enregistrement
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserRegister(): bool {
    return $this->userModel->getName()
      && $this->userModel->getPassword()
      && $this->userModel->getMailAddress();
  }


  /**
   * Contrôle des données de l'utilisateur à la connexion
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserLogin(): bool {
    return $this->userModel->getPassword() && $this->userModel->getMailAddress();
  }
}
