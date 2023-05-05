<?php

class BaseController {
  /**
   * Affichage de la réponse de l'API
   *
   * @param string $header Entête de la réponse
   * @param array $body Contenu de la réponse
   */
  protected function sendResponse(string $header, mixed $body = []): void {
    header($header);
    echo json_encode($body);
  }


  /**
   * Réponse de l'API : Méthode non supportée
   */
  protected function methodNotSupported(): void {
    $error = array("error" => "Method not supported");
    $this->sendResponse("HTTP/1.1 405 Method Not Allowed", $error);
  }


  /**
   * Réponse de l'API : Contenu de la requête non valide
   */
  protected function invalidBody(): void {
    $error = array("error" => "Invalid body");
    $this->sendResponse("HTTP/1.1 422 Unprocessable Entity", $error);
  }


  /**
   * Génération de noms aléatoires
   *
   * @param integer $number Nombre de noms à générer
   * @return array Tableau des noms générés
   */
  protected function randomName(int $number): array {
    $consonants = ["b", "c", "d", "f", "g", "h", "j", "k", "l", "l", "m", "m", "n", "n", "p", "r", "r", "s", "s", "t", "t", "v", "w", "x", "y", "z"];
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
        }
      }

      array_push($names, $name);
    }

    return $names;
  }
}
