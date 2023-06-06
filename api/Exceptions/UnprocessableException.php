<?php

namespace App\Exceptions;

class UnprocessableException extends HttpException {
  public function __construct($message = "Contenu de la requête non valide") {
    parent::__construct("HTTP/1.1 422 Unprocessable Entity", $message, 422);
  }
}
