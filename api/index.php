<?php

require "./config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$body = (array) json_decode(file_get_contents("php://input"), TRUE);
$requestMethod = $_SERVER["REQUEST_METHOD"];

$params[0] = isset($_GET["one"]) ? $_GET["one"] : "";
$params[1] = isset($_GET["two"]) ? $_GET["two"] : "";

try {
  // Gestion des utilisateurs
  if (preg_match("/\/api\/users.*$/", $uri)) {
    $controller = new UserController($requestMethod, $params, $body);
    $controller->processRequest($uri);
    return;
  }
} catch (Exception $e) {
  header("HTTP/1.1 500 Internal Server Error");
  echo json_encode($e->getMessage());
  exit();
}

header("HTTP/1.1 404 Not Found");
exit();
