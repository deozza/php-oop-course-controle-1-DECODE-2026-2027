<?php

namespace App\Controllers;

use App\Services\ContactStorage;
use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactDeleteController extends AbstractController
{
    public function process(Request $request): Response
    {
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

        if ($storage->delete($filename) === false) {
            $response = new Response();
            $response->sendResponse(500, json_encode(["error" => "Could not delete file"]));

            return $response;
        }

        $response = new Response();
        $response->sendResponse(204, "");

        return $response;
    }
}