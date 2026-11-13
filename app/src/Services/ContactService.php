<?php

namespace App\Services;

use App\Entities\Contact;
use App\Forms\ContactForm;

class ContactService {

    public const CONTACT_DIR = __DIR__ . '/../../var/contacts/';

    public function __construct()
    {
        if (!is_dir(self::CONTACT_DIR)) {
            mkdir(self::CONTACT_DIR, 0777, true);
        }
    }

    public function buildContact(array $payload): Contact
    {
        $contact = new Contact();
        $contact->email = $payload['email'];
        $contact->subject = $payload['subject'];
        $contact->message = $payload['message'];

        return $contact;
    }

    public function saveContact(Contact $contact): void
    {
        $contactForm = new ContactForm();
        $data = $contact->toArray();

        if ($contactForm->checkContactOutputIsValid($data) === false) {
            throw new \RuntimeException('Invalid contact output data');
        }

        $filename = $contact->getFilename();
        file_put_contents(
            self::CONTACT_DIR . $filename,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function getAllContacts(): array
    {
        if (!is_dir(self::CONTACT_DIR)) {
            return [];
        }

        $files = glob(self::CONTACT_DIR . '*.json') ?: [];
        $contacts = [];
        $contactForm = new ContactForm();

        foreach ($files as $filePath) {
            if (!is_file($filePath)) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);
            if ($contactForm->checkContactOutputIsValid($data)) {
                $contacts[] = [
                    'email' => $data['email'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                    'dateOfCreation' => (int) $data['dateOfCreation'],
                    'dateOfLastUpdate' => (int) $data['dateOfLastUpdate'],
                ];
            }
        }

        return $contacts;
    }

    public function findContactFilePath(string $filename): ?string
    {
        $sanitized = basename(rawurldecode(trim($filename)));
        if ($sanitized === '' || $sanitized === '.' || $sanitized === '..') {
            return null;
        }

        $withJson = str_ends_with($sanitized, '.json') ? $sanitized : $sanitized . '.json';
        $directPath = self::CONTACT_DIR . $withJson;

        if (is_file($directPath)) {
            return $directPath;
        }

        if (!is_dir(self::CONTACT_DIR)) {
            return null;
        }

        $withoutJson = substr($withJson, 0, -5);
        $files = glob(self::CONTACT_DIR . '*.json') ?: [];

        foreach ($files as $filePath) {
            if (!is_file($filePath)) {
                continue;
            }

            $base = basename($filePath);
            if (str_starts_with($base, $withoutJson . '.')) {
                return $filePath;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);
            if (!is_array($data) || !isset($data['dateOfCreation'], $data['email'])) {
                continue;
            }

            $ts = (int) $data['dateOfCreation'];
            $email = (string) $data['email'];
            $tsFilename = sprintf(Contact::CONTACT_FILENAME_FORMAT, $ts, $email);
            $id24 = sprintf(Contact::CONTACT_ID_FORMAT, date('Y-m-d_H-i-s', $ts), $email);
            $id12 = sprintf(Contact::CONTACT_ID_FORMAT, date('Y-m-d_h-i-s', $ts), $email);

            if ($withJson === $tsFilename || $withJson === $id24 || $withJson === $id12) {
                return $filePath;
            }
        }

        return null;
    }

    public function getContactByFilename(string $filename): ?array
    {
        $filePath = $this->findContactFilePath($filename);
        if ($filePath === null) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        $contactForm = new ContactForm();
        if ($contactForm->checkContactOutputIsValid($data) === false) {
            return null;
        }

        return [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => (int) $data['dateOfCreation'],
            'dateOfLastUpdate' => (int) $data['dateOfLastUpdate'],
        ];
    }

    public function updateContact(string $filename, array $payload): ?array
    {
        $filePath = $this->findContactFilePath($filename);
        if ($filePath === null) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        $contactForm = new ContactForm();
        if ($contactForm->checkContactOutputIsValid($data) === false) {
            return null;
        }

        foreach (ContactForm::EXPECTED_PAYLOAD_PROPERTIES as $field) {
            if (array_key_exists($field, $payload)) {
                $data[$field] = $payload[$field];
            }
        }

        $data['dateOfCreation'] = (int) $data['dateOfCreation'];
        $data['dateOfLastUpdate'] = time();

        if ($contactForm->checkContactOutputIsValid($data) === false) {
            throw new \RuntimeException('Invalid updated contact output data');
        }

        file_put_contents(
            $filePath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $data;
    }

    public function deleteContact(string $filename): bool
    {
        $filePath = $this->findContactFilePath($filename);
        if ($filePath === null || !is_file($filePath)) {
            return false;
        }

        return unlink($filePath);
    }
}
