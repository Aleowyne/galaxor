<?php

namespace App\Exceptions;

use Exception;

class HttpException extends Exception {
  protected $headerResponse;
  protected $bodyResponse;

  public function __construct(
    string $headerResponse = "HTTP/1.1 500 Internal Server Error",
    string $message = "Erreur interne du serveur",
    int $code = 500
  ) {
    $this->headerResponse = $headerResponse;
    $this->bodyResponse = ["error" => $message];

    parent::__construct($message, $code);
  }

  public function getHeader() {
    return $this->headerResponse;
  }

  public function getBody() {
    return $this->bodyResponse;
  }
}
