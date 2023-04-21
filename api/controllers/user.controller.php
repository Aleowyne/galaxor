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
  public function processRequest(string $uri) {
    /* Endpoints : 
      - /api/users
      - /api/users/:id */
    if (preg_match("/\/api\/users(\/[0-9]*)?$/", $uri)) {
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
   * Récupération d'un ou plusieurs utilisateurs
   *
   * @return void
   */
  private function getUsers() {
    $userId = (int) $this->params[0];

    if ($this->requestMethod != "GET") {
      $error = array("error" => "Method not supported");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    // Récupération des données d'un utilisateur
    if ($userId != 0) {
      $result = $this->userModel->findOne(["id" => $userId]);

      if (!$result) {
        $this->sendResponse("HTTP/1.1 404 Not Found");
        return;
      }
      // Récupération des données de tous les utilisateurs
    } else {
      $result = $this->userModel->findAll();
    };

    $this->sendResponse("HTTP/1.1 200 OK", $result);
  }

  /**
   * Enregistrer un utilisateur
   *
   * @return void
   */
  private function registerUser() {
    if ($this->requestMethod != "POST") {
      $error = array("error" => "Method not supported");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    // Données de l'utilisateur non valides
    if (!$this->validateUserRegister($this->body)) {
      $error = array("error" => "Invalid body");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    // Utilisateur déjà enregistré
    if ($this->userModel->findOne(["name" => $this->body["name"]])) {
      $error = array("error" => "Invalid body");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    $result = $this->userModel->insertOne($this->body);

    if (!$result) {
      $this->sendResponse("HTTP/1.1 500 Internal Server Error");
    } else {
      $this->sendResponse("HTTP/1.1 201 Created");
    }
  }

  /**
   * Connexion d'un utilisateur
   *
   * @return void
   */
  private function login() {
    if ($this->requestMethod != "POST") {
      $error = array("error" => "Method not supported");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    // Données de l'utilisateur non valide
    if (!$this->validateUserLogin($this->body)) {
      $error = array("error" => "Invalid body");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    $userId = $this->userModel->login($this->body);

    if ($userId == 0) {
      $this->sendResponse("HTTP/1.1 404 Not Found");
    } else {
      $this->sendResponse("HTTP/1.1 200 OK");
      $_SESSION["id"] = $userId;
      $_SESSION["name"] = $this->body["name"];
    }
  }

  /**
   * Déconnexion d'un utilisateur
   *
   * @return void
   */
  private function logout() {
    if ($this->requestMethod != "POST") {
      $error = array("error" => "Method not supported");
      $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
      return;
    }

    $this->sendResponse("HTTP/1.1 200 OK");
    $_SESSION = array();
    session_destroy();
  }

  /**
   * Validation des données de l'utilisateur à l'enregistrement
   *
   * @param array $body Données de l'utilisateur
   * @return boolean Flag indiquant si les données sont valides
   */
  private function validateUserRegister($body) {
    if (
      isset($body["name"]) && isset($body["password"]) && isset($body["mail_address"])
      && $body["name"] !== "" && $body["password"] !== "" && $body["mail_address"] !== ""
    ) {
      return true;
    }

    return false;
  }

  /**
   * Validation des données de l'utilisateur à la connexion
   *
   * @param array $body Données de l'utilisateur
   * @return boolean Flag indiquant si les données sont valides
   */
  private function validateUserLogin($body) {
    if (
      isset($body["name"]) && isset($body["password"])
      && $body["name"] !== "" && $body["password"] !== ""
    ) {
      return true;
    }

    return false;
  }
}
