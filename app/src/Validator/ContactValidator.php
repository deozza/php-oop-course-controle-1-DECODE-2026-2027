<?php

namespace App\Validator;

use Lib\Exception\HttpException;

class ContactValidator {

    public const array EDITABLE_FIELDS = ['email', 'subject', 'message'];
    public const array STORED_FIELDS = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];

    private const int EMAIL_MAX_LENGTH = 254;
    private const int SUBJECT_MAX_LENGTH = 255;
    private const int MESSAGE_MAX_LENGTH = 5000;

    public function validateCreation(array $body): array
    {
        $errors = $this->checkUnknownFields($body, self::EDITABLE_FIELDS);

        foreach (self::EDITABLE_FIELDS as $field) {
            if (array_key_exists($field, $body) === false) {
                $errors[] = sprintf('the field "%s" is required', $field);
            }
        }

        return $this->validateFields($body, $errors);
    }

    public function validateUpdate(array $body): array
    {
        $errors = $this->checkUnknownFields($body, self::EDITABLE_FIELDS);

        if ($body === []) {
            $errors[] = sprintf('at least one of these fields is required: %s', implode(', ', self::EDITABLE_FIELDS));
        }

        return $this->validateFields($body, $errors);
    }

    public function isValidStoredContact(mixed $data): bool
    {
        if (is_array($data) === false) {
            return false;
        }

        $keys = array_keys($data);
        sort($keys);
        $expected = self::STORED_FIELDS;
        sort($expected);

        if ($keys !== $expected) {
            return false;
        }

        if ($this->getFieldErrors($data) !== []) {
            return false;
        }

        foreach (['dateOfCreation', 'dateOfLastUpdate'] as $dateField) {
            if (is_int($data[$dateField]) === false || $data[$dateField] < 0) {
                return false;
            }
        }

        return $data['dateOfLastUpdate'] >= $data['dateOfCreation'];
    }

    private function checkUnknownFields(array $body, array $allowedFields): array
    {
        $errors = [];

        foreach (array_keys($body) as $field) {
            if (in_array($field, $allowedFields, true) === false) {
                $errors[] = sprintf('the field "%s" is not allowed', $field);
            }
        }

        return $errors;
    }

    private function validateFields(array $body, array $errors): array
    {
        $errors = [...$errors, ...$this->getFieldErrors($body)];

        if ($errors !== []) {
            throw new HttpException(400, 'invalid request body', $errors);
        }

        $values = [];
        foreach (self::EDITABLE_FIELDS as $field) {
            if (array_key_exists($field, $body)) {
                $values[$field] = trim($body[$field]);
            }
        }

        return $values;
    }

    private function getFieldErrors(array $data): array
    {
        $errors = [];

        if (array_key_exists('email', $data)) {
            $errors = [...$errors, ...$this->getEmailErrors($data['email'])];
        }

        if (array_key_exists('subject', $data)) {
            $errors = [...$errors, ...$this->getTextErrors('subject', $data['subject'], self::SUBJECT_MAX_LENGTH)];
        }

        if (array_key_exists('message', $data)) {
            $errors = [...$errors, ...$this->getTextErrors('message', $data['message'], self::MESSAGE_MAX_LENGTH)];
        }

        return $errors;
    }

    private function getEmailErrors(mixed $email): array
    {
        if (is_string($email) === false) {
            return ['the field "email" must be a string'];
        }

        $email = trim($email);

        if ($email === '') {
            return ['the field "email" must not be empty'];
        }

        if (mb_strlen($email) > self::EMAIL_MAX_LENGTH) {
            return [sprintf('the field "email" must not exceed %d characters', self::EMAIL_MAX_LENGTH)];
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || preg_match('#[/\\\\]#', $email) === 1) {
            return ['the field "email" must be a valid email address'];
        }

        return [];
    }

    private function getTextErrors(string $field, mixed $value, int $maxLength): array
    {
        if (is_string($value) === false) {
            return [sprintf('the field "%s" must be a string', $field)];
        }

        if (mb_check_encoding($value, 'UTF-8') === false) {
            return [sprintf('the field "%s" must be valid utf-8', $field)];
        }

        $value = trim($value);

        if ($value === '') {
            return [sprintf('the field "%s" must not be empty', $field)];
        }

        if (mb_strlen($value) > $maxLength) {
            return [sprintf('the field "%s" must not exceed %d characters', $field, $maxLength)];
        }

        return [];
    }
}
