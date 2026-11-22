<?php

namespace App\Controllers;

use App\Services\ContactStorage;
use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactSelectController extends AbstractController
{
    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

        $requestParams = $request->getParams();

        $filename = rawurldecode($requestParams['filename']);

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

        try {
            $content = $storage->read($filename);
        } catch (\JsonException $e) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "Error reading JSON file"]));

            return $response;
        }

        $response = new Response();
        $response->sendResponse(200, json_encode($content));

        return $response;
    }
}
