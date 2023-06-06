<?php

namespace App\Exceptions;

class NotFoundException extends HttpException {
  public function __construct($message = "Non trouvé") {
    parent::__construct("HTTP/1.1 404 Not Found", $message, 404);
  }
}
