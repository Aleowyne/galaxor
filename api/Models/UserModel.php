<?php

namespace App\Models;

class UserModel {
  public int $id;
  public string $name;
  public string $mailAddress;
  public string $password;

  /**
   * Constructeur
   *
   * @param mixed[] $user Données de l'utilisateur
   */
  public function __construct(array $user = []) {
    $this->id = $user["id"] ?? 0;
    $this->name = $user["name"] ?? "";
    $this->mailAddress = $user["mail_address"] ?? "";
    $this->password = $user["password"] ?? "";
  }

  /**
   * Transformation des données utilisateur sous forme de tableau
   *
   * @return mixed[] Données de l'utilisateur
   */
  public function toArray(): array {
    return [
      "id" => $this->id,
      "name" => $this->name,
      "mail_address" => $this->mailAddress
    ];
  }
}
