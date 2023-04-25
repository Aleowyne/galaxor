<?php

class Database {
  private $connection = null;
  private $lastIds = [];

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
      throw $e;
    }
  }

  public function getConnection() {
    return $this->connection;
  }

  /**
   * Requête de sélection sur la base de données
   *
   * @param string $query Requête
   * @param array $params Paramètres de la requête
   * @return array Réponse de la requête
   */
  public function select($query = "", $params = []) {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->closeCursor();

      return empty($result) ? [] : $result;
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Requête d'insertion sur la base de données
   *
   * @param string $query Requête
   * @param array $params Paramètres de la requête
   * @return array Liste des IDs insérés
   */
  public function insert($query = "", $params = []) {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $this->lastIds;
      $stmt->closeCursor();

      return $result;
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Exécution d'une requête
   *
   * @param string $query Requête
   * @param array $params Paramètres de la requête
   * @return PDOStatement Requête préparée
   */
  private function executeStatement($query = "", $params = []) {
    try {
      $this->lastIds = [];
      $stmt = $this->connection->prepare($query);

      if ($stmt === false) {
        throw new Exception("Unable to do prepared statement: $query");
      }

      // Pas de paramètre
      if (!$params) {
        $stmt->execute();
        return $stmt;
      }

      // Au moins un paramètre
      foreach ($params as $param) {
        $stmt->execute($param);
        array_push($this->lastIds, (int) $this->connection->lastInsertId());
      }

      return $stmt;
    } catch (Exception $e) {
      throw $e;
    }
  }
}
