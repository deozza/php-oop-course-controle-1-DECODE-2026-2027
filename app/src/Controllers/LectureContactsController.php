<?php

namespace App\Controllers;

use App\Services\StockageContacts;
use Lib\Http\Request;
use Lib\Http\Response;

class LectureContactsController extends AbstractControllerJson {

    public function process(Request $request): Response {
        $nomDuFichier = $this->recuperer_le_nom_de_fichier_dans_la_query_string();

        if($nomDuFichier === null) {
            return $this->repondre_avec_la_liste_de_tous_les_contacts();
        }

        return $this->repondre_avec_un_seul_contact($nomDuFichier);
    }

    private function repondre_avec_la_liste_de_tous_les_contacts(): Response {
        $stockage = new StockageContacts();
        $contacts = $stockage->recuperer_tous_les_contacts_enregistres();

        return $this->creer_reponse_json(200, $contacts);
    }

    private function repondre_avec_un_seul_contact(string $nomDuFichier): Response {
        $stockage = new StockageContacts();
        $contact = $stockage->recuperer_un_contact_par_son_nom_de_fichier($nomDuFichier);

        if($contact === null) {
            return $this->creer_reponse_erreur_json(404, 'contact not found');
        }

        return $this->creer_reponse_json(200, $contact);
    }
}
