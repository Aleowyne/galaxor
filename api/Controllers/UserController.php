<?php

namespace App\Controllers;

use App\Dao\UserDao;
use App\Models\UserModel;
use App\Exceptions;

class UserController extends BaseController {
  private UserDao $userDao;

  /**
   * Constructeur
   */
  public function __construct() {
    $this->userDao = new UserDao();
  }


  /**
   * Récupération d'un utilisateur
   *
   * @param integer $userId Identifiant de l'utilisateur
   * @return UserModel Données de l'utilisateur
   */
  public function getUser(int $userId): UserModel {
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
   * @param string $name Nom de l'utilisateur
   * @param string $password Mot de passe
   * @param string $mailAddress Adresse mail
   * @return UserModel Données de l'utilisateur
   */
  public function register(string $name, string $password, string $mailAddress): UserModel {
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
   * @param string $mailAddress Adresse mail
   * @param string $password Mot de passe
   * @return UserModel Données de l'utilisateur
   */
  public function login(string $mailAddress, string $password): UserModel {
    $user = $this->userDao->login($mailAddress, $password);

    if (!$user->id) {
      throw new Exceptions\NotFoundException("Connexion a échoué");
    }

    return $user;
  }
}
