<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class ContactController extends AbstractController
{
    private const string CONTACTS_DIRECTORY = __DIR__ . '/../../var/contacts';

    public function process(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            $path = parse_url($request->getUri(), PHP_URL_PATH);
            if (is_string($path) && $path !== '/contact') {
                return $this->getContact($path);
            }

            return $this->getAllContacts();
        }

        if ($request->getMethod() === 'DELETE') {
            return $this->deleteContact($request);
        }

        if ($request->getMethod() === 'PATCH') {
            return $this->updateContact($request);
        }

        if (!$this->hasJsonContentType($request)) {
            return $this->jsonResponse(415, ['error' => 'Content-Type must be application/json']);
        }

        try {
            $contact = json_decode($request->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->jsonResponse(400, ['error' => 'Invalid JSON']);
        }

        if (!is_object($contact)) {
            return $this->jsonResponse(400, ['error' => 'The request body must be a JSON object']);
        }

        $data = get_object_vars($contact);
        $expectedFields = ['email', 'subject', 'message'];
        $receivedFields = array_keys($data);
        sort($expectedFields);
        sort($receivedFields);

        if ($receivedFields !== $expectedFields) {
            return $this->jsonResponse(400, ['error' => 'Only email, subject and message are allowed']);
        }

        if (
            !is_string($data['email'])
            || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false
            || !is_string($data['subject'])
            || trim($data['subject']) === ''
            || !is_string($data['message'])
            || trim($data['message']) === ''
        ) {
            return $this->jsonResponse(400, ['error' => 'Invalid contact data']);
        }

        if (!is_dir(self::CONTACTS_DIRECTORY) && !mkdir(self::CONTACTS_DIRECTORY, 0775, true) && !is_dir(self::CONTACTS_DIRECTORY)) {
            return $this->jsonResponse(500, ['error' => 'Could not create the contacts directory']);
        }

        $timestamp = time();
        $safeEmail = preg_replace('/[^a-zA-Z0-9@._+-]/', '_', $data['email']);
        $fileName = $timestamp . '_' . $safeEmail . '.json';
        while (file_exists(self::CONTACTS_DIRECTORY . '/' . $fileName)) {
            $timestamp++;
            $fileName = $timestamp . '_' . $safeEmail . '.json';
        }

        $contactData = [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => (string) $timestamp,
            'dateOfLastUpdate' => (string) $timestamp,
        ];

        $saved = file_put_contents(
            self::CONTACTS_DIRECTORY . '/' . $fileName,
            json_encode($contactData, JSON_THROW_ON_ERROR),
            LOCK_EX
        );

        if ($saved === false) {
            return $this->jsonResponse(500, ['error' => 'Could not save the contact']);
        }

        $responseFileName = date('Y-m-d_H-i-s', $timestamp) . '_' . $safeEmail . '.json';

        return $this->jsonResponse(201, ['file' => $responseFileName]);
    }

    private function getAllContacts(): Response
    {
        if (!is_dir(self::CONTACTS_DIRECTORY)) {
            return $this->jsonResponse(200, []);
        }

        $files = glob(self::CONTACTS_DIRECTORY . '/*.json');
        if ($files === false) {
            return $this->jsonResponse(500, ['error' => 'Could not list contacts']);
        }

        $contacts = [];
        foreach ($files as $file) {
            if (!is_readable($file)) {
                return $this->jsonResponse(500, ['error' => 'Could not read a contact']);
            }

            $content = file_get_contents($file);
            if ($content === false) {
                return $this->jsonResponse(500, ['error' => 'Could not read a contact']);
            }

            try {
                $contact = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return $this->jsonResponse(500, ['error' => 'A contact file contains invalid JSON']);
            }

            if (!$this->isValidSavedContact($contact)) {
                return $this->jsonResponse(500, ['error' => 'A contact file contains invalid data']);
            }

            $contacts[] = $contact;
        }

        return $this->jsonResponse(200, $contacts);
    }

    private function getContact(string $path): Response
    {
        $file = $this->getContactFilePath($path);
        if ($file === null || !is_file($file)) {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        if (!is_readable($file)) {
            return $this->jsonResponse(500, ['error' => 'Could not read the contact']);
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return $this->jsonResponse(500, ['error' => 'Could not read the contact']);
        }

        try {
            $contact = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->jsonResponse(500, ['error' => 'The contact file contains invalid JSON']);
        }

        if (!$this->isValidSavedContact($contact)) {
            return $this->jsonResponse(500, ['error' => 'The contact file contains invalid data']);
        }

        return $this->jsonResponse(200, $contact);
    }

    private function deleteContact(Request $request): Response
    {
        $path = parse_url($request->getUri(), PHP_URL_PATH);
        $file = is_string($path) ? $this->getContactFilePath($path) : null;
        if ($file === null || !is_file($file)) {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        if (!unlink($file)) {
            return $this->jsonResponse(500, ['error' => 'Could not delete the contact']);
        }

        $response = new Response();
        $response->statusCode = 204;
        $response->content = '';

        return $response;
    }

    private function updateContact(Request $request): Response
    {
        if (!$this->hasJsonContentType($request)) {
            return $this->jsonResponse(415, ['error' => 'Content-Type must be application/json']);
        }

        $path = parse_url($request->getUri(), PHP_URL_PATH);
        $file = is_string($path) ? $this->getContactFilePath($path) : null;
        if ($file === null || !is_file($file)) {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        if (!is_readable($file)) {
            return $this->jsonResponse(500, ['error' => 'Could not read the contact']);
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return $this->jsonResponse(500, ['error' => 'Could not read the contact']);
        }

        try {
            $contact = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->jsonResponse(500, ['error' => 'The contact file contains invalid JSON']);
        }

        if (!$this->isValidSavedContact($contact)) {
            return $this->jsonResponse(500, ['error' => 'The contact file contains invalid data']);
        }

        try {
            $updates = json_decode($request->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->jsonResponse(400, ['error' => 'Invalid JSON']);
        }

        if (!is_object($updates)) {
            return $this->jsonResponse(400, ['error' => 'The request body must be a JSON object']);
        }

        $updates = get_object_vars($updates);
        if ($updates === [] || array_diff(array_keys($updates), ['email', 'subject', 'message']) !== []) {
            return $this->jsonResponse(400, ['error' => 'Only email, subject and message can be updated']);
        }

        foreach ($updates as $field => $value) {
            if (!is_string($value) || trim($value) === '') {
                return $this->jsonResponse(400, ['error' => 'Invalid contact data']);
            }

            if ($field === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                return $this->jsonResponse(400, ['error' => 'Invalid contact data']);
            }
        }

        $contactData = get_object_vars($contact);
        foreach ($updates as $field => $value) {
            $contactData[$field] = $value;
        }
        $contactData['dateOfLastUpdate'] = (string) time();

        $saved = file_put_contents($file, json_encode($contactData, JSON_THROW_ON_ERROR), LOCK_EX);
        if ($saved === false) {
            return $this->jsonResponse(500, ['error' => 'Could not update the contact']);
        }

        return $this->jsonResponse(200, (object) $contactData);
    }

    private function getContactFilePath(string $path): ?string
    {
        $filename = rawurldecode(basename($path));
        if (
            str_contains($filename, '/')
            || str_contains($filename, '\\')
            || preg_match('/^\d+_[a-zA-Z0-9@._+-]+\.json$/D', $filename) !== 1
        ) {
            return null;
        }

        return self::CONTACTS_DIRECTORY . '/' . $filename;
    }

    private function hasJsonContentType(Request $request): bool
    {
        foreach ($request->getHeaders() as $name => $value) {
            if (strtolower($name) === 'content-type') {
                return strtolower(trim(explode(';', $value)[0])) === 'application/json';
            }
        }

        return false;
    }

    private function isValidSavedContact(mixed $contact): bool
    {
        if (!is_object($contact)) {
            return false;
        }

        $data = get_object_vars($contact);
        $expectedFields = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];
        $receivedFields = array_keys($data);
        sort($expectedFields);
        sort($receivedFields);

        return $receivedFields === $expectedFields
            && is_string($data['email'])
            && filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false
            && is_string($data['subject'])
            && trim($data['subject']) !== ''
            && is_string($data['message'])
            && trim($data['message']) !== ''
            && is_string($data['dateOfCreation'])
            && ctype_digit($data['dateOfCreation'])
            && is_string($data['dateOfLastUpdate'])
            && ctype_digit($data['dateOfLastUpdate']);
    }

    private function jsonResponse(int $statusCode, mixed $data): Response
    {
        $response = new Response();
        $response->statusCode = $statusCode;
        $response->headers['Content-Type'] = 'application/json';
        $response->content = json_encode($data, JSON_THROW_ON_ERROR);

        return $response;
    }
}
