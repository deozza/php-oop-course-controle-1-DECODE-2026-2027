<?php

namespace App\Services;

class StockageContacts {

    private const string DOSSIER_DES_CONTACTS = __DIR__ . '/../../var/contacts';

    public function enregistrer_un_nouveau_contact_dans_un_fichier(array $donneesDuContact): string {
        $this->creer_le_dossier_des_contacts_s_il_n_existe_pas();

        $timestamp = time();
        $nomDuFichier = $timestamp . '_' . $donneesDuContact['email'] . '.json';

        $contenu = [
            'email' => $donneesDuContact['email'],
            'subject' => $donneesDuContact['subject'],
            'message' => $donneesDuContact['message'],
            'dateOfCreation' => $timestamp,
            'dateOfLastUpdate' => $timestamp,
        ];

        $this->ecrire_le_contenu_dans_le_fichier($nomDuFichier, $contenu);

        return $nomDuFichier;
    }

    public function recuperer_tous_les_contacts_enregistres(): array {
        if(is_dir(self::DOSSIER_DES_CONTACTS) === false) {
            return [];
        }

        $contacts = [];

        foreach(glob(self::DOSSIER_DES_CONTACTS . '/*.json') as $cheminDuFichier) {
            $contact = $this->lire_et_decoder_un_fichier_de_contact($cheminDuFichier);

            if($contact !== null) {
                $contacts[] = $contact;
            }
        }

        return $contacts;
    }

    public function recuperer_un_contact_par_son_nom_de_fichier(string $nomDuFichier): ?array {
        if($this->verifier_que_le_nom_de_fichier_est_sans_danger($nomDuFichier) === false) {
            return null;
        }

        $cheminDuFichier = self::DOSSIER_DES_CONTACTS . '/' . $nomDuFichier;

        if(is_file($cheminDuFichier) === false) {
            return null;
        }

        return $this->lire_et_decoder_un_fichier_de_contact($cheminDuFichier);
    }

    public function modifier_un_contact_existant(string $nomDuFichier, array $modifications): ?array {
        $contact = $this->recuperer_un_contact_par_son_nom_de_fichier($nomDuFichier);

        if($contact === null) {
            return null;
        }

        foreach($modifications as $nomDuChamp => $nouvelleValeur) {
            $contact[$nomDuChamp] = $nouvelleValeur;
        }

        $contact['dateOfLastUpdate'] = time();

        $this->ecrire_le_contenu_dans_le_fichier($nomDuFichier, $contact);

        return $contact;
    }

    public function supprimer_le_fichier_d_un_contact(string $nomDuFichier): bool {
        if($this->recuperer_un_contact_par_son_nom_de_fichier($nomDuFichier) === null) {
            return false;
        }

        unlink(self::DOSSIER_DES_CONTACTS . '/' . $nomDuFichier);

        return true;
    }

    private function verifier_que_le_nom_de_fichier_est_sans_danger(string $nomDuFichier): bool {
        if($nomDuFichier !== basename($nomDuFichier)) {
            return false;
        }

        return str_ends_with($nomDuFichier, '.json') && str_starts_with($nomDuFichier, '.') === false;
    }

    private function lire_et_decoder_un_fichier_de_contact(string $cheminDuFichier): ?array {
        $contenu = file_get_contents($cheminDuFichier);

        if($contenu === false) {
            return null;
        }

        try {
            $contact = json_decode($contenu, true, 512, JSON_THROW_ON_ERROR);
        } catch(\JsonException $e) {
            return null;
        }

        if($this->verifier_que_le_contact_lu_a_la_bonne_structure($contact) === false) {
            return null;
        }

        return $contact;
    }

    private function verifier_que_le_contact_lu_a_la_bonne_structure(mixed $contact): bool {
        if(is_array($contact) === false) {
            return false;
        }

        $clesAttendues = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];

        $clesDuContact = array_keys($contact);
        sort($clesDuContact);
        sort($clesAttendues);

        return $clesDuContact === $clesAttendues;
    }

    private function ecrire_le_contenu_dans_le_fichier(string $nomDuFichier, array $contenu): void {
        file_put_contents(
            self::DOSSIER_DES_CONTACTS . '/' . $nomDuFichier,
            json_encode($contenu, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    private function creer_le_dossier_des_contacts_s_il_n_existe_pas(): void {
        if(is_dir(self::DOSSIER_DES_CONTACTS) === false) {
            mkdir(self::DOSSIER_DES_CONTACTS, 0775, true);
        }
    }
}
