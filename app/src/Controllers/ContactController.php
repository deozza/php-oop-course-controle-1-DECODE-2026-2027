<?php

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

class ContactController extends AbstractController
{
    private function errorResponse(string $message): Response
    {
        $response = new Response();
        $response->statusCode = 400;
        $response->content = json_encode(['error' => $message]);
        return $response;
    }
    private function serverErrorResponse(string $message): Response
    {
        $response = new Response();
        $response->statusCode = 500;
        $response->content = json_encode(['error' => $message]);
        return $response;
    }
    private function validResponse(string $filename): Response
    {
        $response = new Response();
        $response->statusCode = 201;
        $response->content = json_encode(['file' => $filename]);
        return $response;
    }
    public function process(Request $request): Response
    {

        $body = $request->getBody();
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->errorResponse('Invalid JSON format');
        }
        if (is_array($data) === false) {
            return $this->errorResponse('Invalid data format');
        }
        $hasRequiredProperties = array_key_exists('email', $data)
            && array_key_exists('subject', $data)
            && array_key_exists('message', $data);
        $allowedProperties = ['email', 'subject', 'message'];
        $receivedProperties = array_keys($data);
        $unknownProperties = array_diff($receivedProperties, $allowedProperties);
        if ($hasRequiredProperties === false) {
            return $this->errorResponse('Missing required properties');
        }
        if (!empty($unknownProperties) === true) {
            return $this->errorResponse('Unknown properties');
        }
        $timestamp = time();
        $dateOfCreation = $timestamp;
        $dateOfLastUpdate = $timestamp;
        $contactData = [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $dateOfCreation,
            'dateOfLastUpdate' => $dateOfLastUpdate
        ];
        $contactsDirectory = __DIR__ . '/../../var/contacts/';
        $filename = $timestamp . '_' . $data['email'] . '.json';
        $filePath = $contactsDirectory . $filename;
        $json = json_encode($contactData);
        if (!is_dir($contactsDirectory)) {
            mkdir($contactsDirectory);
        }
        $verifFile = file_put_contents($filePath, $json);
        if ($verifFile === false) {
            return $this->serverErrorResponse('Unable to save contact');
        }
        $file =
            date('Y-m-d_H-i-s', $timestamp) .
            '_' .
            $data['email'] .
            '.json';

        return $this->validResponse($file);
    }
}
