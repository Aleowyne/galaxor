<?php
class UserModel extends Database {
  /**
   * Sélection de tous les utilisateurs en base
   *
   * @return array Liste des utilisateurs
   */
  public function findAll(): array {
    return $this->select("SELECT id, name, mail_address FROM user ORDER BY id ASC");
  }

  /**
   * Sélection d'un utilisateur en base
   *
   * @param array $params Identifiant ou nom de l'utilisateur
   * @return array Données de l'utilisateur
   */
  public function findOne(array $params): array {
    if (isset($params["id"])) {
      $field = "id = :id";
    } else {
      $field = "name = :name";
    }

    return $this->select(
      "SELECT id, name, mail_address FROM user WHERE $field",
      [$params]
    );
  }

  /**
   * Ajout d'un utilisateur dans la base
   *
   * @param array $params Données de l'utilisateur
   * @return integer ID de l'utilisateur
   */
  public function insertOne(array $params): int {
    $ids = $this->insert(
      "INSERT INTO user (name, password, mail_address) VALUES (:name, :password, :mail_address)",
      [$params]
    );

    return (count($ids) === 1) ? $ids[0] : 0;
  }

  /**
   * Connexion d'un utilisateur
   *
   * @param array $params Nom de l'utilisateur
   * @return array Données de l'utilisateur
   */
  public function login(array $params): array {
    return $this->select(
      "SELECT id, password FROM user WHERE name = :name",
      [$params]
    );
  }
}
