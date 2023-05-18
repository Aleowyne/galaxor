<?php

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
  public function __construct(string $requestMethod, array $params, array $body) {
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
    /* Endpoint /api/users/:id */
    if (preg_match("/\/api\/users\/[0-9]*$/", $uri)) {
      switch ($this->requestMethod) {
        case "GET":
          $this->getUser();
          break;
        default:
          $this->sendMethodNotSupported();
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
          $this->sendMethodNotSupported();
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
          $this->sendMethodNotSupported();
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
          $this->sendMethodNotSupported();
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
          $this->sendMethodNotSupported();
      }
      return;
    }

    $this->sendErrorResponse("URL non valide");
  }


  /**
   * Récupération d'un utilisateur
   */
  private function getUser(): void {
    $userId = (int) ($this->params[0] ?? 0);

    $user = $this->userDao->findOneById($userId);

    if ($user->id) {
      $this->sendSuccessResponse($user->toArray());
    } else {
      $this->sendErrorResponse("Utilisateur non trouvé");
    }
  }


  /**
   * Récupération de plusieurs utilisateurs
   */
  private function getUsers(): void {
    $users = $this->userDao->findAll();

    $arrayUsers = [
      "users" => array_map(function (UserModel $user) {
        return $user->toArray();
      }, $users)
    ];

    $this->sendSuccessResponse($arrayUsers);
  }


  /**
   * Enregistrer un utilisateur
   */
  private function register(): void {
    // Données de l'utilisateur non valides
    if (!$this->checkUserRegister()) {
      $this->sendInvalidBody();
      return;
    }

    $name = $this->body["name"];
    $password = $this->body["password"];
    $mailAddress = $this->body["mail_address"];

    // Utilisateur déjà enregistré
    if (
      $this->userDao->findOneByAddress($mailAddress)->id
      || $this->userDao->findOneByName($password)->id
    ) {
      $this->sendInvalidBody();
      return;
    }

    $user = $this->userDao->insertOne($name, $password, $mailAddress);

    if ($user->id) {
      $this->sendCreatedResponse($user->toArray());
    } else {
      $this->sendInternalServerError("Erreur à l'inscription");
    }
  }


  /**
   * Connexion d'un utilisateur
   */
  private function login(): void {
    // Utilisateur déjà connecté
    if (isset($_SESSION["id"]) && $_SESSION["id"]) {
      $userSession = new UserModel($_SESSION);
      $this->sendSuccessResponse($userSession->toArray());
      return;
    }

    // Données de l'utilisateur non valide
    if (!$this->checkUserLogin()) {
      $this->sendInvalidBody();
      return;
    }

    $mailAddress = $this->body["mail_address"];
    $password = $this->body["password"];

    $user = $this->userDao->login($mailAddress, $password);

    if ($user->id) {
      $_SESSION = $user;
      $this->sendSuccessResponse($user->toArray());
    } else {
      $this->sendErrorResponse("Connexion échouée");
    }
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
   * Contrôle des données de l'utilisateur à l'enregistrement
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserRegister(): bool {
    $mailAddress = $this->body["mail_address"] ?? "";
    $name = $this->body["name"] ?? "";
    $password = $this->body["password"] ?? "";

    return $mailAddress && $name && $password;
  }


  /**
   * Contrôle des données de l'utilisateur à la connexion
   *
   * @return boolean Flag indiquant si les données sont valides
   */
  private function checkUserLogin(): bool {
    $mailAddress = $this->body["mail_address"] ?? "";
    $password = $this->body["password"] ?? "";

    return $mailAddress && $password;
  }
}
