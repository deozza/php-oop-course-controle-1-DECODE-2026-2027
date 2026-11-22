<?php

namespace App\Controllers;

use App\Services\StockageContacts;
use Lib\Http\Request;
use Lib\Http\Response;

class SuppressionContactController extends AbstractControllerJson {

    public function process(Request $request): Response {
        $nomDuFichier = $this->recuperer_le_nom_de_fichier_dans_la_query_string();
        $stockage = new StockageContacts();

        if($nomDuFichier === null || $stockage->supprimer_le_fichier_d_un_contact($nomDuFichier) === false) {
            return $this->creer_reponse_erreur_json(404, 'contact not found');
        }

        $response = new Response();
        $response->statusCode = 204;

        return $response;
    }
}
