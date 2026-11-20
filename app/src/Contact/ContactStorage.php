<?php

namespace App\Contact;

class ContactStorage
{
    public const FIELDS = ['email', 'subject', 'message'];
    private string $dir;

    public function __construct()
    {
        $this->dir = __DIR__ . '/../../var/contacts';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    public function validate(array $data, bool $partial = false): bool
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as $key => $value) {
            if (!in_array($key, self::FIELDS, true)) {
                return false;
            }
            if (!is_string($value) || trim($value) === '') {
                return false;
            }
        }

        if (!$partial) {
            foreach (self::FIELDS as $field) {
                if (!isset($data[$field])) {
                    return false;
                }
            }
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            if (str_contains($data['email'], '/') || str_contains($data['email'], '\\')) {
                return false;
            }
        }

        return true;
    }

    public function isValidFilename(string $file): bool
    {
        return $file === basename($file) && preg_match('/^\d+_.+\.json$/', $file) === 1;
    }

    public function create(array $data): ?string
    {
        $now = time();
        $file = $now . '_' . $data['email'] . '.json';

        $saved = $this->write($file, [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $now,
            'dateOfLastUpdate' => $now,
        ]);

        return $saved ? $file : null;
    }

    public function findAll(): array
    {
        $contacts = [];
        $files = glob($this->dir . '/*.json');

        if ($files === false) {
            return [];
        }

        foreach ($files as $path) {
            $content = json_decode(file_get_contents($path), true);
            if (is_array($content)) {
                $contacts[] = $content;
            }
        }

        return $contacts;
    }

    public function find(string $file): ?array
    {
        if (!$this->isValidFilename($file)) {
            return null;
        }

        $path = $this->dir . '/' . $file;
        if (!is_file($path)) {
            return null;
        }

        $content = json_decode(file_get_contents($path), true);
        return is_array($content) ? $content : null;
    }

    public function update(string $file, array $changes): ?array
    {
        $content = $this->find($file);
        if ($content === null) {
            return null;
        }

        foreach ($changes as $key => $value) {
            $content[$key] = $value;
        }
        $content['dateOfLastUpdate'] = time();

        return $this->write($file, $content) ? $content : null;
    }

    public function delete(string $file): bool
    {
        if ($this->find($file) === null) {
            return false;
        }

        return unlink($this->dir . '/' . $file);
    }

    private function write(string $file, array $content): bool
    {
        $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return file_put_contents($this->dir . '/' . $file, $json) !== false;
    }
}
