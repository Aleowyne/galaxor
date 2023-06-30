<?php

namespace App\Dao;

use App\Core\Database;
use App\Models\UserModel;

class UserDao extends Database {
  /**
   * Sélection de tous les utilisateurs en base
   *
   * @return UserModel[] Liste des utilisateurs
   */
  public function findAll(): array {
    $result = $this->select(
      "SELECT id, name, mail_address FROM user ORDER BY id ASC"
    );

    return array_map(function (array $res) {
      return new UserModel($res);
    }, $result);
  }


  /**
   * Sélection d'un utilisateur en base en fonction de l'ID
   *
   * @param integer $id Identifiant de l'utilisateur
   * @return UserModel Données de l'utilisateur
   */
  public function findOneById(int $id): UserModel {
    $params = [["id" => $id]];

    $result = $this->select(
      "SELECT id, name, mail_address FROM user WHERE id = :id",
      $params
    );

    return new UserModel($result[0] ?? []);
  }


  /**
   * Sélection d'un utilisateur en base en fonction de l'adresse mail
   *
   * @param string $mailAddress Adresse mail de l'utilisateur
   * @return UserModel Données de l'utilisateur
   */
  public function findOneByAddress(string $mailAddress): UserModel {
    $params = [["mail_address" => $mailAddress]];

    $result = $this->select(
      "SELECT id, name, mail_address FROM user WHERE mail_address = :mail_address",
      $params
    );

    return new UserModel($result[0] ?? []);
  }


  /**
   * Sélection d'un utilisateur en base en fonction du nom
   *
   * @param string $name Nom de l'utilisateur
   * @return UserModel Données de l'utilisateur
   */
  public function findOneByName(string $name): UserModel {
    $params = [["name" => $name]];

    $result = $this->select(
      "SELECT id, name, mail_address FROM user WHERE name = :name",
      $params
    );

    return new UserModel($result[0] ?? []);
  }


  /**
   * Ajout d'un utilisateur dans la base
   *
   * @param string $name Nom de l'utilisateur
   * @param string $password Mot de passe
   * @param string $mailAddress Adresse mail
   * @return UserModel Données de l'utilisateur
   */
  public function insertOne(string $name, string $password, string $mailAddress): UserModel {
    $params = [[
      "name" => $name,
      "mail_address" => $mailAddress,
      "password" => password_hash($password, PASSWORD_DEFAULT)
    ]];

    $result = $this->insert(
      "INSERT INTO user (name, password, mail_address) VALUES (:name, :password, :mail_address)",
      $params
    );

    $user = new UserModel();
    $user->id = $result[0] ?? 0;
    $user->name = $name;
    $user->mailAddress = $mailAddress;

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
    $params = [["mail_address" => $mailAddress]];

    $result = $this->select(
      "SELECT id, name, password, mail_address FROM user WHERE mail_address = :mail_address",
      $params
    );

    $user = new UserModel($result);

    // Vérification du mot de passe
    if (password_verify($password, $result[0]["password"] ?? "")) {
      $user->id = $result[0]["id"] ?? 0;
      $user->name = $result[0]["name"] ?? "";
      $user->mailAddress = $result[0]["mail_address"] ?? "";
    } else {
      $user->id = 0;
    }

    return $user;
  }
}
