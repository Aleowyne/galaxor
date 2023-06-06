<?php

namespace App\Exceptions;

class InternalErrorException extends HttpException {
  public function __construct($message = "Erreur interne du serveur") {
    parent::__construct("HTTP/1.1 500 Internal Server Error", $message, 500);
  }
}
