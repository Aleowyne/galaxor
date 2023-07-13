<?php

namespace App\Routes;

use App\Controllers\UserController;
use App\Models\UserModel;
use App\Exceptions;

class UserRoute extends BaseRoute {
  private UserController $userController;
  private string $requestMethod;
  private array $params;
  private array $body;

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param mixed[] $params Paramètres de la requête
   * @param mixed[] $body Contenu de la requête
   */
  public function __construct(string $requestMethod = "", array $params = [], array $body = []) {
    $this->userController = new UserController();
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
    // Endpoint /api/users/:id
    if (preg_match("/\/api\/users\/\d+$/", $uri)) {
      // Récupération d'un utilisateur
      if ($this->requestMethod === "GET") {
        $this->requestGetUser();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users
    elseif (preg_match("/\/api\/users$/", $uri)) {
      // Récupération des utilisateurs
      if ($this->requestMethod === "GET") {
        $this->requestGetUsers();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users/register
    elseif (preg_match("/\/api\/users\/register$/", $uri)) {
      // Enregistrement d'un utilisateur
      if ($this->requestMethod === "POST") {
        $this->requestRegister();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users/login
    elseif (preg_match("/\/api\/users\/login$/", $uri)) {
      // Connexion d'un utilisateur
      if ($this->requestMethod === "POST") {
        $this->requestLogin();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users/logout
    elseif (preg_match("/\/api\/users\/logout$/", $uri)) {
      // Déconnexion d'un utilisateur
      if ($this->requestMethod === "POST") {
        $this->requestLogout();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    } else {
      throw new Exceptions\NotFoundException("URL non valide");
    }
  }

  /**
   * Requête récupération d'un utilisateur
   */
  private function requestGetUser(): void {
    $userId = (int) ($this->params[0] ?? 0);
    $user = $this->userController->getUser($userId);
    $this->sendSuccessResponse($user->toArray());
  }


  /**
   * Requête récupération d'une liste d'utilisateurs
   */
  private function requestGetUsers(): void {
    $users = $this->userController->getUsers();

    $arrayUsers = [
      "users" => array_map(function (UserModel $user) {
        return $user->toArray();
      }, $users)
    ];

    $this->sendSuccessResponse($arrayUsers);
  }


  /**
   * Requête enregistrement d'un utilisateur
   */
  private function requestRegister(): void {
    // Données de l'utilisateur non valides
    if (!$this->checkBodyRegister()) {
      throw new Exceptions\UnprocessableException();
    }

    $name = $this->body["name"];
    $password = $this->body["password"];
    $mailAddress = $this->body["mail_address"];

    $user = $this->userController->register($name, $password, $mailAddress);

    $this->sendSuccessResponse($user->toArray());
  }


  /**
   * Requête connexion d'un utilisateur
   */
  private function requestLogin(): void {
    $user = $_SESSION["user"] ?? new UserModel();

    // Si l'utilisateur n'est pas connecté
    if ($user->id === 0) {
      // Données de l'utilisateur non valide
      if (!$this->checkBodyLogin()) {
        throw new Exceptions\UnprocessableException();
      }

      $mailAddress = $this->body["mail_address"];
      $password = $this->body["password"];

      $user = $this->userController->login($mailAddress, $password);

      $_SESSION["user"] = $user;
    }

    $this->sendSuccessResponse($user->toArray());
  }


  /**
   * Requête déconnexion d'un utilisateur
   */
  private function requestLogout(): void {
    $_SESSION = [];
    session_destroy();

    $this->sendNoContentResponse();
  }


  /**
   * Contrôle du contenu de la requête lors de l'enregistrement de l'utilisateur
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyRegister(): bool {
    $mailAddress = $this->body["mail_address"] ?? "";
    $name = $this->body["name"] ?? "";
    $password = $this->body["password"] ?? "";

    return (bool) ($mailAddress && $name && $password);
  }


  /**
   * Contrôle du contenu de la requête lors de la connexion de l'utilisateur
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyLogin(): bool {
    $mailAddress = $this->body["mail_address"] ?? "";
    $password = $this->body["password"] ?? "";

    return (bool) ($mailAddress && $password);
  }
}
