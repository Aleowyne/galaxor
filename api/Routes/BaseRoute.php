<?php

namespace App\Routes;

use App\Models\UserModel;

class BaseRoute {
  /**
   * Affichage de la réponse de l'API
   *
   * @param string $header Entête de la réponse
   * @param mixed[] $body Contenu de la réponse
   */
  public static function sendResponse(string $header, array $body = []): void {
    header($header);
    echo json_encode($body);
  }

  /**
   * Réponse de l'API : Succès
   * @param mixed[] $body Contenu de la reponse
   */
  protected function sendSuccessResponse(array $body = []): void {
    $this->sendResponse("HTTP/1.1 200 OK", $body);
  }

  /**
   * Réponse de l'API : Créé
   * @param mixed[] $body Contenu de la reponse
   */
  protected function sendCreatedResponse(array $body = []): void {
    $this->sendResponse("HTTP/1.1 201 Created", $body);
  }

  /**
   * Réponse de l'API : Pas de contenu
   * @param mixed[] $body Contenu de la reponse
   */
  protected function sendNoContentResponse(): void {
    $this->sendResponse("HTTP/1.1 204 No Content");
  }

  /**
   * Contrôle des autorisations
   *
   * @param integer $userId Identifiant de l'utilisateur déterminé à partir de la requête
   * @return boolean Flag indiquant si le contrôle est validé
   */
  protected function checkAuth(int $userId): bool {
    $sessionUser = $_SESSION["user"] ?? new UserModel();
    return $sessionUser->id !== 0 && $sessionUser->id === $userId;
  }
}
