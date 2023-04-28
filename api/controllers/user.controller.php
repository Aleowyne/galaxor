<?php

class UserController extends BaseController {
  private $userModel = null;
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
    $this->userModel = new UserModel();
    $this->requestMethod = $requestMethod;
    $this->params = $params;
    $this->body = $body;
  }

  /**
   * Traitement de la requête
   *
   * @param string $uri URI
   * @return void
   */
  public function processRequest(string $uri): void {
    /* Endpoint /api/users/:id */
    if (preg_match("/\/api\/users\/[0-9]*$/", $uri)) {
      $this->getUser();
      return;
    }

    /* Endpoint /api/users */
    if (preg_match("/\/api\/users$/", $uri)) {
      $this->getUsers();
      return;
    }

    // Endpoint /api/users/register
    if (preg_match("/\/api\/users\/register$/", $uri)) {
      $this->registerUser();
      return;
    }

    // Endpoint /api/users/login
    if (preg_match("/\/api\/users\/login$/", $uri)) {
      $this->login();
      return;
    }

    // Endpoint /api/users/logout
    if (preg_match("/\/api\/users\/logout$/", $uri)) {
      $this->logout();
      return;
    }

    $this->sendResponse("HTTP/1.1 404 Not Found");
  }

  /**
   * Récupération d'un utilisateur
   *
   * @return void
   */
  private function getUser(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $userId = ["id" => (int) $this->params[0]];

    $result = $this->userModel->findOneById($userId);

    if ($result) {
      $this->sendResponse("HTTP/1.1 200 OK", $result);
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Récupération de plusieurs utilisateurs
   *
   * @return void
   */
  private function getUsers(): void {
    if (!$this->checkMethod($this->requestMethod, ['GET'])) {
      return;
    }

    $result = $this->userModel->findAll();

    $this->sendResponse("HTTP/1.1 200 OK", $result);
  }

  /**
   * Enregistrer un utilisateur
   *
   * @return void
   */
  private function registerUser(): void {
    if (!$this->checkMethod($this->requestMethod, ['POST'])) {
      return;
    }

    // Données de l'utilisateur non valides
    if (!$this->checkUserRegister($this->body)) {
      $this->invalidBody();
      return;
    }

    $mailAddress = ["mail_address" => $this->body["mail_address"]];
    $username = ["name" => $this->body["name"]];

    // Utilisateur déjà enregistré
    if ($this->userModel->findOneByAddress($mailAddress) || $this->userModel->findOneByName($username)) {
      $this->invalidBody();
      return;
    }

    $user = [
      "name" => $this->body["name"],
      "mail_address" => $this->body["mail_address"],
      "password" => password_hash($this->body["password"], PASSWORD_DEFAULT)
    ];

    $userId = $this->userModel->insertOne($user);

    if ($userId != 0) {
      $this->sendResponse("HTTP/1.1 201 Created", ["id" => $userId]);
    } else {
      $this->sendResponse("HTTP/1.1 500 Internal Server Error");
    }
  }

  /**
   * Connexion d'un utilisateur
   *
   * @return void
   */
  private function login(): void {
    if (!$this->checkMethod($this->requestMethod, ['POST'])) {
      return;
    }

    // Données de l'utilisateur non valide
    if (!$this->checkUserLogin($this->body)) {
      $this->invalidBody();
      return;
    }

    $mailAddress = ["mail_address" => $this->body["mail_address"]];

    $result = $this->userModel->login($mailAddress);

    // Contrôle du mot de passe
    if ($this->checkPassword($this->body["password"], $result[0]["password"] ?? "")) {
      $user = [
        "id" => $result[0]["id"],
        "name" => $result[0]["name"]
      ];

      $this->sendResponse("HTTP/1.1 200 OK", $user);
      $_SESSION["id"] = $user["id"];
      $_SESSION["name"] = $user["name"];
    } else {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    }
  }

  /**
   * Déconnexion d'un utilisateur
   *
   * @return void
   */
  private function logout(): void {
    if (!$this->checkMethod($this->requestMethod, ['POST'])) {
      return;
    }

    $this->sendResponse("HTTP/1.1 200 OK");
    $_SESSION = array();
    session_destroy();
  }

  /**
   * Contrôle des données de l'utilisateur à l'enregistrement
   *
   * @param array $body Données de l'utilisateur
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserRegister(array $body): bool {
    return ($body["name"] ?? false) && ($body["password"] ?? false) && ($body["mail_address"] ?? false);
  }

  /**
   * Contrôle des données de l'utilisateur à la connexion
   *
   * @param array $body Données de l'utilisateur
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserLogin(array $body): bool {
    return ($body["mail_address"] ?? false) && ($body["password"] ?? false);
  }

  /**
   * Contrôle du mot de passe
   *
   * @param string $password Mot de passe saisi
   * @param string $passDatabase Mot de passe en base
   * @return boolean Flag indiquant si le mot de passe saisi est correct
   */
  private function checkPassword($password, $passDatabase): bool {
    return password_verify($password, $passDatabase);
  }
}
