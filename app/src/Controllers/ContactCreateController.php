<?php

namespace App\Controllers;


use App\Services\{ContactStorage, ContactValidator};
use Lib\Http\{JsonBody, Request, Response};
use Lib\Controllers\AbstractController;

class ContactCreateController extends AbstractController
{

    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

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

        $response = new Response();
        $validator = new ContactValidator();

        if ($validator->missingKeys($json) === [] && $validator->unknownKeys($json) === []) {
            if ($validator->isValidEmail($json["email"]) === false) {
                $response->sendResponse(400, json_encode(["error" => "The email provided is invalid"]));
                return $response;
            }

            if ($validator->isString($json["subject"]) === false) {
                $response->sendResponse(400, json_encode(["error" => "\"subject\" is not a string"]));
                return $response;
            }
            if ($validator->isString($json["message"]) === false) {
                $response->sendResponse(400, json_encode(["error" => "\"message\" is not a string"]));
                return $response;
            }

            try {
                $timestamp = time();

                $name = date("Y-m-d_H-i-s") . "_" . $json["email"];

                $data = array_merge($json, ["dateOfCreation" => $timestamp, 'dateOfLastUpdate' => $timestamp]);

                $storage = new ContactStorage();




                if ($storage->save($name, $data) === false) {
                    $response->sendResponse(500, json_encode(["error" => "could not write file"]));
                    return $response;
                }

                $response->sendResponse(201, json_encode(["file" => $name . ".json"]));
                return $response;
            } catch (\JsonException $e) {
                $response->sendResponse(400, json_encode(["error" => "Invalid JSON"]));
                return $response;
            } catch (\Throwable $e) {
                $response->sendResponse(500, json_encode(["error" => "Could not write JSON file"]));
                return $response;
            }
        } else {
            $missing = $validator->missingKeys($json);
            $invalid = $validator->unknownKeys($json);
            $response->statusCode = 400;

            if ($missing !== [] && $invalid !== []) {
                $response->content = json_encode(["error" => "invalid JSON schema: '" . implode(", ", $invalid) . "' is/are invalid and '" . implode(", ", $missing) . "' is/are missing."]);
            } elseif ($missing !== []) {
                $response->content = json_encode(["error" => "invalid JSON schema: '" . implode(", ", $missing) . "' key is/are missing."]);
            } else {
                $response->content = json_encode(["error" => "invalid JSON schema: '" . implode(", ", $invalid) . "' key is/are invalid."]);
            }

        }


        return $response;
    }
}
