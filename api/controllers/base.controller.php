<?php

class BaseController {
  /**
   * Affichage de la réponse de l'API
   *
   * @param string $header Entête de la réponse
   * @param mixed $body Contenu de la réponse
   * @return void
   */
  protected function sendResponse(string $header, mixed $body = null) {
    header($header);

    if ($body) {
      echo json_encode($body);
    }
  }
}
