<?php

namespace App\Forms;

class ContactForm {

    public const EXPECTED_PAYLOAD_PROPERTIES = ['email', 'subject', 'message'];
    public const EXPECTED_CONTACT_PROPERTIES = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];

    public function checkPayloadIsValid(mixed $payload): bool
    {
        if (!is_array($payload)) {
            return false;
        }

        if (count(self::EXPECTED_PAYLOAD_PROPERTIES) !== count($payload)) {
            return false;
        }

        if (
            !empty(array_diff(self::EXPECTED_PAYLOAD_PROPERTIES, array_keys($payload))) ||
            !empty(array_diff(array_keys($payload), self::EXPECTED_PAYLOAD_PROPERTIES))
        ) {
            return false;
        }

        foreach (self::EXPECTED_PAYLOAD_PROPERTIES as $property) {
            if (!is_string($payload[$property]) || trim($payload[$property]) === '') {
                return false;
            }
        }

        if (filter_var($payload['email'], FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    public function checkUpdatePayloadIsValid(mixed $payload): bool
    {
        if (!is_array($payload) || empty($payload)) {
            return false;
        }

        $extraKeys = array_diff(array_keys($payload), self::EXPECTED_PAYLOAD_PROPERTIES);
        if (!empty($extraKeys)) {
            return false;
        }

        foreach ($payload as $key => $value) {
            if (!is_string($value) || trim($value) === '') {
                return false;
            }
            if ($key === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                return false;
            }
        }

        return true;
    }

    public function checkContactOutputIsValid(mixed $data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        if (count(self::EXPECTED_CONTACT_PROPERTIES) !== count($data)) {
            return false;
        }

        if (
            !empty(array_diff(self::EXPECTED_CONTACT_PROPERTIES, array_keys($data))) ||
            !empty(array_diff(array_keys($data), self::EXPECTED_CONTACT_PROPERTIES))
        ) {
            return false;
        }

        if (
            !is_string($data['email']) ||
            filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false ||
            !is_string($data['subject']) ||
            trim($data['subject']) === '' ||
            !is_string($data['message']) ||
            trim($data['message']) === ''
        ) {
            return false;
        }

        if (!is_int($data['dateOfCreation']) && !is_numeric($data['dateOfCreation'])) {
            return false;
        }

        if (!is_int($data['dateOfLastUpdate']) && !is_numeric($data['dateOfLastUpdate'])) {
            return false;
        }

        return true;
    }
}
