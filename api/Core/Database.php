<?php

namespace App\Core;

use App\Exceptions;
use PDO;
use PDOStatement;
use Exception;

class Database {
  private $connection = null;
  private $lastIds = [];
  private $nbRows = 0;

  public function __construct() {
    $host = DB_HOST;
    $port = DB_PORT;
    $database = DB_NAME;
    $user = DB_USERNAME;
    $pass = DB_PASSWORD;

    try {
      $this->connection = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$database",
        $user,
        $pass
      );
    } catch (Exception $e) {
      throw new Exceptions\InternalErrorException($e);
    }
  }


  /**
   * Requête de sélection sur la base de données
   *
   * @param string $query Requête
   * @param mixed[] $params Paramètres de la requête
   * @return mixed[] Réponse de la requête
   */
  public function select(string $query = "", array $params = []): array {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->closeCursor();

      return empty($result) ? [] : $result;
    } catch (Exception $e) {
      throw new Exceptions\InternalErrorException($e);
    }
  }


  /**
   * Requête d'insertion sur la base de données
   *
   * @param string $query Requête
   * @param mixed[] $params Paramètres de la requête
   * @return int[] Liste des IDs insérés
   */
  public function insert(string $query = "", array $params = []): array {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $this->lastIds;
      $stmt->closeCursor();

      return $result;
    } catch (Exception $e) {
      throw new Exceptions\InternalErrorException($e);
    }
  }


  /**
   * Requête de mise à jour sur la base de données
   *
   * @param string $query Requête
   * @param mixed[] $params Paramètres de la requête
   * @return boolean Flag indiquant si la mise à jour a réussi
   */
  public function update(string $query = "", array $params = []): bool {
    try {
      $stmt = $this->executeStatement($query, $params);
      $stmt->closeCursor();

      return $this->nbRows === 0 ? false : true;
    } catch (Exception $e) {
      throw new Exceptions\InternalErrorException($e);
    }
  }


  /**
   * Exécution d'une requête
   *
   * @param string $query Requête
   * @param mixed[] $params Paramètres de la requête
   * @return PDOStatement Requête préparée
   */
  private function executeStatement(string $query = "", array $params = []): PDOStatement {
    $this->lastIds = [];
    $this->nbRows = 0;

    $stmt = $this->connection->prepare($query);

    if ($stmt === false) {
      throw new Exceptions\InternalErrorException("Erreur sur la préparation de la requête SQL" . $query);
    }

    // Pas de paramètre
    if (!$params) {
      $stmt->execute();

      $this->lastIds[] = (int) $this->connection->lastInsertId();
      $this->nbRows += $stmt->rowCount();

      return $stmt;
    }

    // Au moins un paramètre
    foreach ($params as $param) {
      $stmt->execute($param);

      $this->lastIds[] = (int) $this->connection->lastInsertId();
      $this->nbRows += $stmt->rowCount();
    }

    return $stmt;
  }
}
