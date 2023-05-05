<?php
class UserModel extends Database {
  private $id = 0;
  private $name = "";
  private $mailAddress = "";
  private $password = "";

  public function setId(string $id): void {
    $this->id = (int) $id;
  }

  public function setName(string $name): void {
    $this->name = $name;
  }

  public function setMailAddress(string $mailAddress): void {
    $this->mailAddress = $mailAddress;
  }

  public function setPassword(string $password): void {
    $this->password = $password;
  }

  public function getId(): int {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getMailAddress(): string {
    return $this->mailAddress;
  }

  public function getPassword(): string {
    return $this->password;
  }


  /**
   * Sélection de tous les utilisateurs en base
   *
   * @return array Liste des utilisateurs
   */
  public function findAll(): array {
    return $this->select(
      "SELECT id, name, mail_address FROM user ORDER BY id ASC"
    );
  }


  /**
   * Sélection d'un utilisateur en base en fonction de l'ID
   *
   * @return array Données de l'utilisateur
   */
  public function findOneById(): array {
    $params = [["id" => $this->id]];

    return $this->select(
      "SELECT id, name, mail_address FROM user WHERE id = :id",
      $params
    );
  }


  /**
   * Sélection d'un utilisateur en base en fonction de l'adresse mail
   *
   * @return array Données de l'utilisateur
   */
  public function findOneByAddress(): array {
    $params = [["mail_address" => $this->mailAddress]];

    return $this->select(
      "SELECT id, name, mail_address FROM user WHERE mail_address = :mail_address",
      $params
    );
  }


  /**
   * Sélection d'un utilisateur en base en fonction du nom
   *
   * @return array Données de l'utilisateur
   */
  public function findOneByName(): array {
    $params = [["name" => $this->name]];

    return $this->select(
      "SELECT id, name, mail_address FROM user WHERE name = :name",
      $params
    );
  }


  /**
   * Ajout d'un utilisateur dans la base
   */
  public function insertOne(): void {
    $params = [[
      "name" => $this->name,
      "mail_address" => $this->mailAddress,
      "password" => password_hash($this->password, PASSWORD_DEFAULT)
    ]];

    $ids = $this->insert(
      "INSERT INTO user (name, password, mail_address) VALUES (:name, :password, :mail_address)",
      $params
    );

    $this->setId($ids[0] ?? 0);
  }


  /**
   * Connexion d'un utilisateur
   *
   * @return bool Flag indiquant si la connexion a réussi
   */
  public function login(): bool {
    $params = [["mail_address" => $this->mailAddress]];

    $result = $this->select(
      "SELECT id, name, password FROM user WHERE mail_address = :mail_address",
      $params
    );

    $this->id = $result[0]["id"] ?? 0;
    $this->name = $result[0]["name"] ?? "";

    return password_verify($this->password, $result[0]["password"] ?? "");
  }
}
