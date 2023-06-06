<?php

namespace App\Controllers;

class BaseController {
  protected const RETURN_STMT = "return ";
  protected const FORMAT_DATE = "Y-m-d H:i:s";

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
   * Génération de noms aléatoires
   *
   * @param integer $number Nombre de noms à générer
   * @return string[] Tableau des noms générés
   */
  protected function randomName(int $number): array {
    $consonants = [
      "b", "c", "d", "f", "g", "h", "j", "k", "l", "l", "m", "m",
      "n", "n", "p", "r", "r", "s", "s", "t", "t", "v", "w", "x", "y", "z"
    ];
    $vowels = ["a", "i", "e", "o", "u"];
    $suffixes = ["une", "ta", "on", "ius", "ter", "ar", "eia", "in", "ax", "nis", "ila", "tis", "ide"];

    $patterns = [
      "Cvcs",
      "Cvcs",
      "Cvcvcs",
      "Cvccvcs",
      "Cvcvcvcs",
      "Vcvcvs"
    ];

    $nbConsonants = count($consonants);
    $nbVowels = count($vowels);
    $nbSuffixes = count($suffixes);
    $nbPatterns = count($patterns);

    $names = [];

    foreach (range(1, $number) as $index) {
      $name = "";
      $pattern = str_split($patterns[rand(0, $nbPatterns - 1)]);

      foreach ($pattern as $letter) {
        switch ($letter) {
          case "C":
            $name .= strtoupper($consonants[rand(0, $nbConsonants - 1)]);
            break;
          case "V":
            $name .= strtoupper($vowels[rand(0, $nbVowels - 1)]);
            break;
          case "c":
            $name .= $consonants[rand(0, $nbConsonants - 1)];
            break;
          case "v":
            $name .= $vowels[rand(0, $nbVowels - 1)];
            break;
          case "s":
            $name .= $suffixes[rand(0, $nbSuffixes - 1)];
            break;
          default:
            break;
        }
      }

      $names[] = $name;
    }

    return $names;
  }
}
