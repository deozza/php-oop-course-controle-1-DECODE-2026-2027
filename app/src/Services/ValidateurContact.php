<?php

namespace App\Services;

class ValidateurContact {

    public const array CHAMPS_AUTORISES = ['email', 'subject', 'message'];

    public function verifier_qu_il_n_y_a_que_des_champs_autorises(array $donnees): bool {
        foreach(array_keys($donnees) as $nomDuChamp) {
            if(in_array($nomDuChamp, self::CHAMPS_AUTORISES, true) === false) {
                return false;
            }
        }

        return true;
    }

    public function verifier_que_tous_les_champs_obligatoires_sont_presents(array $donnees): bool {
        foreach(self::CHAMPS_AUTORISES as $nomDuChamp) {
            if(array_key_exists($nomDuChamp, $donnees) === false) {
                return false;
            }
        }

        return true;
    }

    public function verifier_que_les_valeurs_des_champs_sont_valides(array $donnees): bool {
        foreach($donnees as $nomDuChamp => $valeur) {
            if(is_string($valeur) === false || trim($valeur) === '') {
                return false;
            }

            if($nomDuChamp === 'email' && filter_var($valeur, FILTER_VALIDATE_EMAIL) === false) {
                return false;
            }
        }

        return true;
    }
}
