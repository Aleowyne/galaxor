<?php
class UserModel extends Database {
  /**
   * Sélection de tous les utilisateurs en base
   *
   * @return array|false Réponse de la requête
   */
  public function findAll() {
    return $this->select("SELECT id, name, mail_address FROM user ORDER BY id ASC");
  }

  /**
   * Sélection d'un utilisateur en base à partir d'un identifiant
   *
   * @param array $where Clause where de la requête
   * @return array|false Réponse de la requête
   */
  public function findOne(array $where) {
    if (isset($where["id"])) {
      $field = "id = :id";
    } else {
      $field = "name = :name";
    }

    return $this->select(
      "SELECT id, name, mail_address FROM user WHERE " . $field,
      $where
    );
  }

  /**
   * Ajout d'un utilisateur dans la base
   *
   * @param array $body Données de l'utilisateur
   * @return string|false Réponse de la requête
   */
  public function insertOne(array $body) {
    $params = $body;
    $params["password"] = password_hash($params["password"], PASSWORD_DEFAULT);

    return $this->insert(
      "INSERT INTO user (name, password, mail_address) VALUES (:name, :password, :mail_address)",
      $params
    );
  }

  /**
   * Connexion d'un utilisateur
   *
   * @param array $body Données de l'utilisateur
   * @return integer Réponse de la requête
   */
  public function login(array $body) {
    $result = $this->select(
      "SELECT id, password FROM user WHERE name = :name",
      ["name" => $body["name"]]
    );

    if (isset($result[0]["password"]) && password_verify($body["password"], $result[0]["password"])) {
      return $result[0]["id"];
    }

    return 0;
  }
}
