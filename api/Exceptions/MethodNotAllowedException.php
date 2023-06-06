<?php

namespace App\Exceptions;

class MethodNotAllowedException extends HttpException {
  public function __construct($message = "Méthode non supportée") {
    parent::__construct("HTTP/1.1 405 Method Not Allowed", $message, 405);
  }
}
