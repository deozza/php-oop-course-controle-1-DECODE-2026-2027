<?php

namespace App\Controllers;

use App\Services\StockageContacts;
use App\Services\ValidateurContact;
use Lib\Http\Request;
use Lib\Http\Response;

class ModificationContactController extends AbstractControllerJson {

    public function process(Request $request): Response {
        if($this->verifier_que_le_header_content_type_est_json($request) === false) {
            return $this->creer_reponse_erreur_json(400, 'Content-Type must be application/json');
        }

        $nomDuFichier = $this->recuperer_le_nom_de_fichier_dans_la_query_string();
        $stockage = new StockageContacts();

        if($nomDuFichier === null || $stockage->recuperer_un_contact_par_son_nom_de_fichier($nomDuFichier) === null) {
            return $this->creer_reponse_erreur_json(404, 'contact not found');
        }

        $modifications = $this->decoder_le_corps_json_en_tableau_associatif($request);

        if($modifications === null) {
            return $this->creer_reponse_erreur_json(400, 'invalid json body');
        }

        $validateur = new ValidateurContact();

        if($validateur->verifier_qu_il_n_y_a_que_des_champs_autorises($modifications) === false) {
            return $this->creer_reponse_erreur_json(400, 'only email, subject and message can be updated');
        }

        if($validateur->verifier_que_les_valeurs_des_champs_sont_valides($modifications) === false) {
            return $this->creer_reponse_erreur_json(400, 'invalid values');
        }

        $contactModifie = $stockage->modifier_un_contact_existant($nomDuFichier, $modifications);

        return $this->creer_reponse_json(200, $contactModifie);
    }
}
