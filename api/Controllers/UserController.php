<?php

namespace App\Controllers;

use App\Dao\UserDao;
use App\Models\UserModel;
use App\Exceptions;

class UserController extends BaseController {
  private $userDao = null;
  private string $requestMethod = "";
  private array $params = [];
  private array $body = [];

  /**
   * Constructeur
   *
   * @param string $requestMethod Méthode de la requête
   * @param mixed[] $params Paramètres de la requête
   * @param mixed[] $body Contenu de la requête
   */
  public function __construct(string $requestMethod = "", array $params = [], array $body = []) {
    $this->userDao = new UserDao();
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
        $user = $this->getUser();
        $this->sendSuccessResponse($user->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users
    elseif (preg_match("/\/api\/users$/", $uri)) {
      // Récupération des utilisateurs
      if ($this->requestMethod === "GET") {
        $users = $this->getUsers();

        $arrayUsers = [
          "users" => array_map(function (UserModel $user) {
            return $user->toArray();
          }, $users)
        ];

        $this->sendSuccessResponse($arrayUsers);
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users/register
    elseif (preg_match("/\/api\/users\/register$/", $uri)) {
      // Enregistrement d'un utilisateur
      if ($this->requestMethod === "POST") {
        $user = $this->register();
        $this->sendSuccessResponse($user->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users/login
    elseif (preg_match("/\/api\/users\/login$/", $uri)) {
      // Connexion d'un utilisateur
      if ($this->requestMethod === "POST") {
        $user = $this->login();
        $this->sendSuccessResponse($user->toArray());
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    }

    // Endpoint /api/users/logout
    elseif (preg_match("/\/api\/users\/logout$/", $uri)) {
      // Déconnexion d'un utilisateur
      if ($this->requestMethod === "POST") {
        $this->logout();
      } else {
        throw new Exceptions\MethodNotAllowedException();
      }
    } else {
      throw new Exceptions\NotFoundException("URL non valide");
    }
  }


  /**
   * Récupération d'un utilisateur
   *
   * @param integer $userId Identifiant de l'utilisateur
   * @return UserModel Données de l'utilisateur
   */
  public function getUser(int $userId = 0): UserModel {
    $userId = (int) ($this->params[0] ?? $userId);

    $user = $this->userDao->findOneById($userId);

    if (!$user->id) {
      throw new Exceptions\NotFoundException("Utilisateur non trouvé");
    }

    return $user;
  }


  /**
   * Récupération de plusieurs utilisateurs
   *
   * @return UserModel[] Liste des utilisateurs
   */
  public function getUsers(): array {
    return $this->userDao->findAll();
  }


  /**
   * Enregistrer un utilisateur
   *
   * @return UserModel Données de l'utilisateur
   */
  private function register(): UserModel {
    // Données de l'utilisateur non valides
    if (!$this->checkBodyRegister()) {
      throw new Exceptions\UnprocessableException();
    }

    $name = $this->body["name"];
    $password = $this->body["password"];
    $mailAddress = $this->body["mail_address"];

    // Utilisateur déjà enregistré avec l'adresse mail ou l'utilisateur
    if (
      $this->userDao->findOneByAddress($mailAddress)->id
      || $this->userDao->findOneByName($password)->id
    ) {
      throw new Exceptions\UnprocessableException();
    }

    $user = $this->userDao->insertOne($name, $password, $mailAddress);

    if (!$user->id) {
      throw new Exceptions\InternalErrorException("Inscription a échoué");
    }

    return $user;
  }


  /**
   * Connexion d'un utilisateur
   *
   * @return UserModel Données de l'utilisateur
   */
  private function login(): UserModel {
    // Utilisateur déjà connecté
    if (isset($_SESSION["id"]) && $_SESSION["id"]) {
      return new UserModel($_SESSION);
    }

    // Données de l'utilisateur non valide
    if (!$this->checkBodyLogin()) {
      throw new Exceptions\UnprocessableException();
    }

    $mailAddress = $this->body["mail_address"];
    $password = $this->body["password"];

    $user = $this->userDao->login($mailAddress, $password);

    if (!$user->id) {
      throw new Exceptions\NotFoundException("Connexion a échoué");
    }

    $_SESSION = $user;

    return $user;
  }


  /**
   * Déconnexion d'un utilisateur
   */
  private function logout(): void {
    $_SESSION = array();
    session_destroy();

    $this->sendSuccessResponse();
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

    return $mailAddress && $name && $password;
  }


  /**
   * Contrôle du contenu de la requête lors de la connexion de l'utilisateur
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkBodyLogin(): bool {
    $mailAddress = $this->body["mail_address"] ?? "";
    $password = $this->body["password"] ?? "";

    return $mailAddress && $password;
  }
}
