<?php

use App\Autoload;
use App\Core\Router;

define("PROJECT_ROOT_PATH", __DIR__);

require_once PROJECT_ROOT_PATH . "/config.php";
require_once PROJECT_ROOT_PATH . "/autoload.php";
Autoload::register();

$router = new Router();
$router->start();
