<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Validator\ContactValidator;
use Lib\Exception\HttpException;

class ContactRepository {

    private const string STORAGE_DIR = __DIR__ . '/../../var/contacts';

    private const string FILENAME_PATTERN = '/^\d+_[^\/\\\\\x00]+@[^\/\\\\\x00]+\.json$/';

    private readonly ContactValidator $validator;

    public function __construct()
    {
        $this->validator = new ContactValidator();
    }

    public function create(Contact $contact): string
    {
        $filename = $contact->getDateOfCreation() . '_' . $contact->getEmail() . '.json';
        $path = $this->getStorageDir() . '/' . $filename;

        if (file_exists($path)) {
            throw new HttpException(409, 'a contact form was already sent with this email in the same second');
        }

        $this->write($path, $contact);

        return $filename;
    }

    public function findAll(): array
    {
        $contacts = [];

        foreach (glob($this->getStorageDir() . '/*.json') ?: [] as $path) {
            $contact = $this->read($path);

            if ($contact !== null) {
                $contacts[] = $contact;
            }
        }

        usort($contacts, fn (Contact $a, Contact $b) => $a->getDateOfCreation() <=> $b->getDateOfCreation());

        return $contacts;
    }

    public function find(string $filename): ?Contact
    {
        $path = $this->getPath($filename);

        if ($path === null || is_file($path) === false) {
            return null;
        }

        $contact = $this->read($path);

        if ($contact === null) {
            throw new HttpException(500, 'the stored contact form is corrupted');
        }

        return $contact;
    }

    public function update(string $filename, Contact $contact): void
    {
        $path = $this->getPath($filename);

        if ($path === null || is_file($path) === false) {
            throw new HttpException(404, 'contact form not found');
        }

        $this->write($path, $contact);
    }

    public function delete(string $filename): bool
    {
        $path = $this->getPath($filename);

        if ($path === null || is_file($path) === false) {
            return false;
        }

        if (unlink($path) === false) {
            throw new HttpException(500, 'the contact form could not be deleted');
        }

        return true;
    }

    private function getPath(string $filename): ?string
    {
        if ($filename !== basename($filename) || preg_match(self::FILENAME_PATTERN, $filename) !== 1) {
            return null;
        }

        return $this->getStorageDir() . '/' . $filename;
    }

    private function read(string $path): ?Contact
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return null;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if ($this->validator->isValidStoredContact($data) === false) {
            return null;
        }

        return Contact::fromArray($data);
    }

    private function write(string $path, Contact $contact): void
    {
        $json = json_encode($contact->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new HttpException(500, 'the contact form could not be saved');
        }
    }

    private function getStorageDir(): string
    {
        if (is_dir(self::STORAGE_DIR) === false && mkdir(self::STORAGE_DIR, 0775, true) === false && is_dir(self::STORAGE_DIR) === false) {
            throw new HttpException(500, 'the contact storage folder could not be created');
        }

        return self::STORAGE_DIR;
    }
}
