<?php

namespace App;

class Autoload {
  public static function register() {
    spl_autoload_register([
      __CLASS__,
      'autoload'
    ]);
  }

  /**
   * Inclue le fichier correspondant à notre classe
   * @param string $class Nom de la classe à charger
   */
  public static function autoload($class) {
    $class = str_replace(__NAMESPACE__ . '\\', '', $class);
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/' . $class . '.php';

    if (file_exists($file)) {
      require_once $file;
    }
  }
}
