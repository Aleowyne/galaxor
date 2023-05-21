<?php

date_default_timezone_set('Europe/Paris');

session_start();

define("DB_HOST", "localhost");
define("DB_USERNAME", "user");
define("DB_PASSWORD", "supersecret");
define("DB_NAME", "galaxor");
define("DB_PORT", "3306");

define("PROJECT_ROOT_PATH", __DIR__);

require_once PROJECT_ROOT_PATH . "/models/user.model.php";
require_once PROJECT_ROOT_PATH . "/models/universe.model.php";
require_once PROJECT_ROOT_PATH . "/models/galaxy.model.php";
require_once PROJECT_ROOT_PATH . "/models/solarsystem.model.php";
require_once PROJECT_ROOT_PATH . "/models/planet.model.php";
require_once PROJECT_ROOT_PATH . "/models/item.model.php";
require_once PROJECT_ROOT_PATH . "/models/structure.model.php";
require_once PROJECT_ROOT_PATH . "/models/research.model.php";
require_once PROJECT_ROOT_PATH . "/models/resource.model.php";
require_once PROJECT_ROOT_PATH . "/models/production.model.php";
require_once PROJECT_ROOT_PATH . "/models/cost.model.php";
require_once PROJECT_ROOT_PATH . "/models/prerequisite.model.php";

require_once PROJECT_ROOT_PATH . "/dao/database.php";
require_once PROJECT_ROOT_PATH . "/dao/user.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/universe.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/galaxy.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/solarsystem.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/planet.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/item.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/structure.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/research.dao.php";
require_once PROJECT_ROOT_PATH . "/dao/resource.dao.php";

require_once PROJECT_ROOT_PATH . "/controllers/base.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/user.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/universe.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/galaxy.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/solarsystem.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/planet.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/item.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/structure.controller.php";
require_once PROJECT_ROOT_PATH . "/controllers/resource.controller.php";
