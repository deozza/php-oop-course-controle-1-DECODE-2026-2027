<?php

namespace App\Services;

class ContactValidator
{
    public const array ALLOWED_KEYS = ["email", "subject", "message"];

    public function unknownKeys(array $data): array
    {
        return array_diff(array_keys($data), self::ALLOWED_KEYS);
    }

    public function missingKeys(array $data): array
    {
        return array_diff(self::ALLOWED_KEYS, array_keys($data));
    }

    public function isValidEmail(mixed $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isString(mixed $value): bool
    {
        return is_string($value);
    }
}
