<?php

namespace App\Controllers;

use App\Services\{ContactStorage, ContactValidator};
use Lib\Http\{JsonBody, Request, Response};
use Lib\Controllers\AbstractController;

class ContactPatchController extends AbstractController
{
    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

        $filename = rawurldecode($request->getParams()['filename']);

        $storage = new ContactStorage();

        if ($storage->isValidName($filename) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid filename"]));

            return $response;
        }

        if ($storage->exists($filename) === false) {
            $response = new Response();
            $response->sendResponse(404, json_encode(["error" => "File not found"]));

            return $response;
        }

        if (JsonBody::hasContentType($request) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "No headers provided"]));

            return $response;
        }

        if (JsonBody::isJson($request) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid Content-Type: only JSON content is allowed"]));

            return $response;
        }

        try {
            $json = JsonBody::decode($request);
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid JSON format: Check your request for typos"]));
            
            return $response;
        } catch (\UnexpectedValueException $e) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Invalid Content: Not an array"]));

            return $response;
        }

        $validator = new ContactValidator();
        $invalid = $validator->unknownKeys($json);

        if ($invalid !== []) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "invalid JSON schema: '" . implode(", ", $invalid) . "' key is/are invalid."]));

            return $response;
        }

        if ($json === []) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "Nothing to update: send at least one of email, subject or message"]));

            return $response;
        }


        if (array_key_exists("subject", $json) && $validator->isString($json["subject"]) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "\"subject\" is not a string"]));

            return $response;
        }

        if (array_key_exists("message", $json) && $validator->isString($json["message"]) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "\"message\" is not a string"]));

            return $response;
        }

        if (array_key_exists("email", $json) && $validator->isValidEmail($json["email"]) === false) {
            $response = new Response();
            $response->sendResponse(400, json_encode(["error" => "The email provided is invalid"]));

            return $response;
        }

        try {
            $contact = $storage->read($filename);
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "Error reading JSON file"]));

            return $response;
        }

        $contact = array_merge($contact, $json);
        $contact["dateOfLastUpdate"] = time();

        try {
            $saved = $storage->save($filename, $contact);
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "Error writing JSON file"]));

            return $response;
        }

        if ($saved === false) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "could not write file"]));

            return $response;
        }

        $response = new Response();
        $response->sendResponse(200, json_encode($contact));

        return $response;
    }
}
