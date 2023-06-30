<?php

namespace App\Core;

use Exception;
use App\Exceptions;

class Router {
  public function start() {
    session_start();

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $body = (array) json_decode(file_get_contents("php://input"), true);
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    $params = [];

    foreach ($_GET as $param) {
      $params[] = $param;
    }

    try {
      // Construction du nom de la route à partir de l'URL
      if (preg_match("/\/galaxor\/api\/([a-z]+).*$/", $uri, $match)) {
        $nameRoute = preg_replace("/s$/", "", preg_replace("/ies$/", "y", ucfirst($match[1])));
        $route = "\\App\\Routes\\{$nameRoute}Route";
      } else {
        throw new Exceptions\NotFoundException("URL non trouvée");
      }

      // Appel de la classe route
      if (class_exists($route)) {
        $route = new $route($requestMethod, $params, $body);
        $route->processRequest($uri);
      } else {
        throw new Exceptions\NotFoundException("URL non trouvée");
      }
    } catch (Exceptions\HttpException $e) {
      header($e->getHeader());
      echo json_encode($e->getBody());
    } catch (Exception $e) {
      echo json_encode($e->getMessage());
    }
  }
}
