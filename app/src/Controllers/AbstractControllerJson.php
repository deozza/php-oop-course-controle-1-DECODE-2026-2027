<?php

namespace App\Controllers;

use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

abstract class AbstractControllerJson extends AbstractController {

    protected function creer_reponse_json(int $codeStatut, mixed $donnees): Response {
        $response = new Response();
        $response->statusCode = $codeStatut;
        $response->addHeader('Content-Type', 'application/json');
        $response->content = json_encode($donnees, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $response;
    }

    protected function creer_reponse_erreur_json(int $codeStatut, string $message): Response {
        return $this->creer_reponse_json($codeStatut, ['error' => $message]);
    }

    protected function verifier_que_le_header_content_type_est_json(Request $request): bool {
        $contentType = $request->getHeader('Content-Type');

        if($contentType === null) {
            return false;
        }

        return str_starts_with(strtolower(trim($contentType)), 'application/json');
    }

    protected function decoder_le_corps_json_en_tableau_associatif(Request $request): ?array {
        try {
            $donnees = json_decode($request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch(\JsonException $e) {
            return null;
        }

        if(is_array($donnees) === false || array_is_list($donnees) === true) {
            return null;
        }

        return $donnees;
    }

    protected function recuperer_le_nom_de_fichier_dans_la_query_string(): ?string {
        $nomDuFichier = $_GET['filename'] ?? null;

        if(is_string($nomDuFichier) === false) {
            return null;
        }

        return $nomDuFichier;
    }
}
