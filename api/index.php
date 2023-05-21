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

$params = [];

foreach ($_GET as $param) {
  $params[] = $param;
}

try {
  // Gestion des utilisateurs
  if (preg_match("/\/api\/users.*$/", $uri)) {
    $controller = new UserController($requestMethod, $params, $body);
    $controller->processRequest($uri);
    return;
  }

  // Gestion des planètes
  if (preg_match("/\/api\/planets.*$/", $uri)) {
    $controller = new PlanetController($requestMethod, $params, $body);
    $controller->processRequest($uri);
    return;
  }

  // Gestion des systèmes solaires
  if (preg_match("/\/api\/solarsystems.*$/", $uri)) {
    $controller = new SolarSystemController($requestMethod, $params, $body);
    $controller->processRequest($uri);
    return;
  }

  // Gestion des galaxies
  if (preg_match("/\/api\/galaxies.*$/", $uri)) {
    $controller = new GalaxyController($requestMethod, $params, $body);
    $controller->processRequest($uri);
    return;
  }

  // Gestion des univers
  if (preg_match("/\/api\/universes.*$/", $uri)) {
    $controller = new UniverseController($requestMethod, $params, $body);
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
