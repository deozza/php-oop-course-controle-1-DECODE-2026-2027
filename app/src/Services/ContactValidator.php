<?php

namespace App\Services;

class ContactValidator {
    public const array ALLOWED_FIELDS = ['email', 'subject', 'message'];

    /**
     * Retourne un message d'erreur, ou null si les données sont valides.
     * $partial = true : au moins un champ (PATCH). Sinon : les trois champs (POST).
     */
    public function validate(mixed $data, bool $partial = false): ?string
    {
        if (is_array($data) === false || array_is_list($data) && $data !== []) {
            return 'Body must be a JSON object';
        }

        $unknownFields = array_diff(array_keys($data), self::ALLOWED_FIELDS);
        if ($unknownFields !== []) {
            return 'Unknown field(s): ' . implode(', ', $unknownFields);
        }

        if ($partial === true && $data === []) {
            return 'At least one field is required: ' . implode(', ', self::ALLOWED_FIELDS);
        }

        if ($partial === false) {
            $missingFields = array_diff(self::ALLOWED_FIELDS, array_keys($data));
            if ($missingFields !== []) {
                return 'Missing field(s): ' . implode(', ', $missingFields);
            }
        }

        foreach ($data as $field => $value) {
            if (is_string($value) === false || trim($value) === '') {
                return 'Field "' . $field . '" must be a non empty string';
            }
        }

        if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return 'Field "email" must be a valid email';
        }

        return null;
    }
}
