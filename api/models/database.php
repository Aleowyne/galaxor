<?php

class Database {

  private $connection = null;

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
   * @return array|false Réponse de la requête
   */
  public function select($query = "", $params = []) {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->closeCursor();

      return $result;
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Requête d'insertion sur la base de données
   *
   * @param string $query Requête
   * @param array $params Paramètres de la requête
   * @return string|false Réponse de la requête
   */
  public function insert($query = "", $params = []) {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $this->connection->lastInsertId();
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
   * @param array|false $params Paramètres de la requête
   * @return PDOStatement|false Requête préparée
   */
  private function executeStatement($query = "", $params = []) {
    try {
      $stmt = $this->connection->prepare($query);
      $type = PDO::PARAM_STR;

      if ($stmt === false) {
        throw new Exception("Unable to do prepared statement: " . $query);
      }

      foreach ($params as $key => $value) {
        switch (gettype($key)) {
          case "integer":
            $type = PDO::PARAM_INT;
            break;

          case "boolean":
            $type = PDO::PARAM_BOOL;
            break;

          default:
            $type = PDO::PARAM_STR;
        }

        $stmt->bindValue($key, $value, $type);
      }

      $stmt->execute();

      return $stmt;
    } catch (Exception $e) {
      throw $e;
    }
  }
}
