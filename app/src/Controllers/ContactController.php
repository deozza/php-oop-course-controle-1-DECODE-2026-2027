<?php

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

class ContactController extends AbstractController {
    public function process(Request $request): Response {
        $response = new Response();
        $timestamp = time();

        $headers = $request->getHeaders();

        $dir = self::CONTACTS_DIR;

        if ($headers['Content-Type'] !== 'application/json') {
            $response->statusCode = 400;
            $response->content = json_encode(['error' => 'Content-Type must be application/json']);

            return $response;
        } else {
            $body = file_get_contents('php://input');
            $decodedBody = json_decode($body, true);

            $allowedProperties = ['email', 'subject', 'message'];
            $receivedProperties = is_array($decodedBody) ? array_keys($decodedBody) : [];
            sort($receivedProperties);
            sort($allowedProperties);

            $email = $decodedBody['email'];
            $subject = $decodedBody['subject'];
            $message = $decodedBody['message'];

            if ($receivedProperties !== $allowedProperties) {
                $response->statusCode = 400;
                $response->content = json_encode(['error' => 'Invalid request body']);
                return $response;
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response->statusCode = 400;
                $response->content = json_encode(['error' => 'Invalid email format']);
                return $response;
            } else if ($subject === " " || $subject === "" || $message === " " || $message === "") {
                $response->statusCode = 400;
                $response->content = json_encode(['error' => 'Empty field']);
                return $response;
            }

            if (!is_dir($dir)) {
                mkdir($dir, 755, true);
            }
            
            $fileName = $timestamp.'_'.$email.'.json';
            $fileContent = json_encode(['email' => $email, 'subject' => $subject, 'message' => $message, 'dateOfCreation' => $timestamp, 'dateOfLastUpdate' => $timestamp]);

            file_put_contents($dir . $fileName, $fileContent);

            $response->statusCode = 201;
            $response->content = json_encode(['file' => $fileName]);
            
            return $response;
        }
    }
}