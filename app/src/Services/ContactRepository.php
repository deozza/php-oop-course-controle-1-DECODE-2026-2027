<?php

namespace App\Services;

class ContactRepository {
    private const string STORAGE_PATH = __DIR__ . '/../../var/contacts/';

    public function findAll(): array
    {
        $contacts = [];

        foreach (glob(self::STORAGE_PATH . '*.json') as $file) {
            $contacts[] = json_decode(file_get_contents($file), true);
        }

        return $contacts;
    }

    public function find(string $filename): ?array
    {
        if ($this->exists($filename) === false) {
            return null;
        }

        return json_decode(file_get_contents(self::STORAGE_PATH . $filename), true);
    }

        public function save(string $filename, array $contact): bool
    {
        $path = self::STORAGE_PATH . $filename;

        // Vérifie avant d'écrire : sinon PHP affiche un warning qui casse la réponse
        if (is_writable(file_exists($path) ? $path : self::STORAGE_PATH) === false) {
            return false;
        }

        $json = json_encode($contact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return file_put_contents($path, $json) !== false;
    }

    public function delete(string $filename): bool
    {
        if ($this->exists($filename) === false) {
            return false;
        }

        return unlink(self::STORAGE_PATH . $filename);
    }

    public function exists(string $filename): bool
    {
        // Empêche de sortir du dossier (ex: "../../config/routes.json")
        if (preg_match('/^\d+_[^\/\\\\]+\.json$/', $filename) !== 1) {
            return false;
        }

        return is_file(self::STORAGE_PATH . $filename);
    }
}
