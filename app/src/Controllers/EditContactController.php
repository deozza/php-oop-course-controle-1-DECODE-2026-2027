<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class EditContactController extends AbstractController {
    public function process(Request $request): Response {
        $dir = self::CONTACTS_DIR;
        $fileName = basename($request->getUri());

        $headers = $request->getHeaders();

        if (is_dir($dir)) {
            $contacts = scandir($dir);

            foreach ($contacts as $contact) {

                if ($contact === '.' || $contact === '..') {
                    continue;
                } else if ($contact === $fileName) {

                $response = new Response();

                    if($headers['Content-Type'] !== 'application/json') {
                        $response->statusCode = 400;
                        $response->content = json_encode(['error' => 'Invalid Content-Type. Expected application/json']);
                        return $response;
                    } else {
                        $body = file_get_contents('php://input');
                        $decodedBody = json_decode($body, true);

                        $allowedProperties = ['email', 'subject', 'message'];
                        $receivedProperties = is_array($decodedBody) ? array_keys($decodedBody) : [];
                        sort($receivedProperties);
                        sort($allowedProperties);

                        foreach ($receivedProperties as $property) {
                            if (!in_array($property, $allowedProperties)) {
                                $response->statusCode = 400;
                                $response->content = json_encode(['error' => 'Invalid property: ' . $property]);
                                return $response;
                            } else if ($property === "email" && !filter_var($decodedBody['email'], FILTER_VALIDATE_EMAIL)) {
                            $response->statusCode = 400;
                            $response->content = json_encode(['error' => 'Invalid email format']);
                            return $response;
                            } else if ($decodedBody[$property] === " " || $decodedBody[$property] === "") {
                                $response->statusCode = 400;
                                $response->content = json_encode(['error' => 'Empty field']);
                                return $response;
                            }
                        }

                        $filePath = $dir . $contact;
                        $fileContent = json_decode(file_get_contents($dir . $contact), true);

                        foreach ($receivedProperties as $property) {
                            $fileContent[$property] = $decodedBody[$property];
                        }

                        $fileContent['dateOfLastUpdate']=time();

                        file_put_contents($filePath, json_encode($fileContent));
                    
                        $response->statusCode = 200;
                        $response->content = json_encode($fileContent);

                        return $response;
                    }
                }
            }
        }

        $response = new Response();
        $response->statusCode = 404;
        $response->content = json_encode(['error' => 'Contact not found']);
        return $response;
    }
}