<?php

namespace App\Exceptions;

class UnauthorizedException extends HttpException {
  public function __construct($message = "Accès non autorisé") {
    parent::__construct("HTTP/1.1 401 Unauthorized", $message, 401);
  }
}
