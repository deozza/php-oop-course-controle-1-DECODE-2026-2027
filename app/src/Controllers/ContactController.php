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
    private function notFoundResponse(): Response
    {
        $response = new Response();
        $response->statusCode = 404;
        $response->content = json_encode(['error' => 'Contact not found']);
        return $response;
    }
    private function contactResponse(array $contactData): Response
    {
        $response = new Response();
        $response->statusCode = 200;
        $response->content = json_encode($contactData);
        return $response;
    }
    public function process(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            $contactsDirectory = __DIR__ . '/../../var/contacts/';

            if (isset($_GET['email'])) {
                $email = $_GET['email'];
            } else {
                $email = null;
            }
            $files = scandir($contactsDirectory);
            $contactFound = false;

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $contactsDirectory . $file;
                $fileContent = file_get_contents($filePath);
                $contactData = json_decode($fileContent, true);

                if ($contactData['email'] === $email) {
                    $contactFound = true;
                    return $this->contactResponse($contactData);
                }
            }
            if ($contactFound === false) {
                return $this->notFoundResponse();
            }


            /*$contacts = [];
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $contactsDirectory . $file;
                $fileContent = file_get_contents($filePath);
                $contactData = json_decode($fileContent, true);

                $contacts[] = $contactData;
            }
            return $this->contactResponse($contactData);*/
        } elseif ($request->getMethod() === 'POST') {
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
        } elseif ($request->getMethod() === 'PATCH') {
            $contactsDirectory = __DIR__ . '/../../var/contacts/';

            $body = $request->getBody();
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->errorResponse('Invalid JSON format');
            }
            if (is_array($data) === false) {
                return $this->errorResponse('Invalid data format');
            }
            $allowedProperties = ['email', 'subject', 'message'];
            $receivedProperties = array_keys($data);
            $unknownProperties = array_diff($receivedProperties, $allowedProperties);
            if (!empty($unknownProperties) === true) {
                return $this->errorResponse('Unknown properties');
            }
            if (isset($_GET['email'])) {
                $email = $_GET['email'];
            } else {
                $email = null;
            }

            $files = scandir($contactsDirectory);
            $contactFound = false;
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $contactsDirectory . $file;
                $fileContent = file_get_contents($filePath);
                $contactData = json_decode($fileContent, true);

                if ($contactData['email'] === $email) {
                    $contactFound = true;

                    if (array_key_exists('email', $data)) {
                        $contactData['email'] = $data['email'];
                    }

                    if (array_key_exists('subject', $data)) {
                        $contactData['subject'] = $data['subject'];
                    }

                    if (array_key_exists('message', $data)) {
                        $contactData['message'] = $data['message'];
                    }
                    $timestamp = time();
                    $contactData['dateOfLastUpdate'] = $timestamp;
                    $json = json_encode($contactData);
                    file_put_contents($filePath, $json);
                    return $this->contactResponse($contactData);
                }
            }
            if ($contactFound === false) {
                return $this->notFoundResponse();
            }
        }
    }
}
