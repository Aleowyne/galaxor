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
      // Construction du nom du contrôleur à partir de l'URL
      if (preg_match("/\/galaxor\/api\/([a-z]+).*$/", $uri, $match)) {
        $nameController = preg_replace("/s$/", "", preg_replace("/ies$/", "y", ucfirst($match[1])));
        $controller = "\\App\\Controllers\\{$nameController}Controller";
      } else {
        throw new Exceptions\NotFoundException("URL non trouvée");
      }

      if (class_exists($controller) && property_exists($controller, "requestMethod")) {
        $controller = new $controller($requestMethod, $params, $body);
        $controller->processRequest($uri);
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
