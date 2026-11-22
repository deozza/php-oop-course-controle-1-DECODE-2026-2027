<?php

namespace App\Controllers;

use App\Services\StockageContacts;
use App\Services\ValidateurContact;
use Lib\Http\Request;
use Lib\Http\Response;

class CreationContactController extends AbstractControllerJson {

    public function process(Request $request): Response {
        if($this->verifier_que_le_header_content_type_est_json($request) === false) {
            return $this->creer_reponse_erreur_json(400, 'Content-Type must be application/json');
        }

        $donnees = $this->decoder_le_corps_json_en_tableau_associatif($request);

        if($donnees === null) {
            return $this->creer_reponse_erreur_json(400, 'invalid json body');
        }

        $validateur = new ValidateurContact();

        if($validateur->verifier_qu_il_n_y_a_que_des_champs_autorises($donnees) === false) {
            return $this->creer_reponse_erreur_json(400, 'only email, subject and message are allowed');
        }

        if($validateur->verifier_que_tous_les_champs_obligatoires_sont_presents($donnees) === false) {
            return $this->creer_reponse_erreur_json(400, 'email, subject and message are required');
        }

        if($validateur->verifier_que_les_valeurs_des_champs_sont_valides($donnees) === false) {
            return $this->creer_reponse_erreur_json(400, 'invalid values');
        }

        $stockage = new StockageContacts();
        $nomDuFichier = $stockage->enregistrer_un_nouveau_contact_dans_un_fichier($donnees);

        return $this->creer_reponse_json(201, ['file' => $nomDuFichier]);
    }
}
