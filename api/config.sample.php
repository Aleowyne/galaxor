<?php

session_start();

define("DB_HOST", "localhost");
define("DB_USERNAME", "user");
define("DB_PASSWORD", "supersecret");
define("DB_NAME", "galaxor");
define("DB_PORT", "3306");

define("PROJECT_ROOT_PATH", __DIR__);

require_once PROJECT_ROOT_PATH . "/models/database.php";
require_once PROJECT_ROOT_PATH . "/models/user.model.php";

require_once PROJECT_ROOT_PATH . "/controllers/base.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/user.controller.php";
