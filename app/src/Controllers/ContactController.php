<?php

namespace App\Controllers;

use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class ContactController extends AbstractController
{

    private const string CONTACTS_PATH = __DIR__ . '/../../var/contacts/';

    public function process(Request $request): Response
    {
        $headers = $request->getHeaders();

        if (isset($headers['Content-Type']) === false || $headers['Content-Type'] !== 'application/json') {
            return $this->createResponse(400, ['error' => 'Content-Type must be application/json']);
        }

        $data = json_decode($request->getBody(), true);

        if (is_array($data) === false) {
            return $this->createResponse(400, ['error' => 'Invalid JSON']);
        }

        $expectedKeys = ['email', 'subject', 'message'];
        $receivedKeys = array_keys($data);
        sort($expectedKeys);
        sort($receivedKeys);

        if ($receivedKeys !== $expectedKeys) {
            return $this->createResponse(400, ['error' => 'Only email, subject and message are allowed']);
        }

        foreach ($expectedKeys as $key) {
            if (is_string($data[$key]) === false || trim($data[$key]) === '') {
                return $this->createResponse(400, ['error' => $key . ' must be a non empty string']);
            }
        }

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return $this->createResponse(400, ['error' => 'Invalid email']);
        }

        $timestamp = time();
        $fileName = $timestamp . '_' . $data['email'] . '.json';

        $contact = [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $timestamp,
            'dateOfLastUpdate' => $timestamp
        ];

        file_put_contents(self::CONTACTS_PATH . $fileName, json_encode($contact));

        return $this->createResponse(201, ['file' => $fileName]);
    }

    private function createResponse(int $statusCode, array $content): Response
    {
        $response = new Response();
        $response->statusCode = $statusCode;
        $response->headers['Content-Type'] = 'application/json';
        $response->content = json_encode($content);

        return $response;
    }
}
