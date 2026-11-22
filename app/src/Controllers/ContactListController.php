<?php

namespace App\Controllers;

use App\Services\ContactStorage;
use Lib\Http\{Request, Response};
use Lib\Controllers\AbstractController;

class ContactListController extends AbstractController
{
    public function process(Request $request): Response
    {
        header('Content-Type: application/json; charset=utf-8');

        $storage = new ContactStorage();

        $json = [];
        foreach ($storage->listNames() as $name) {
            try {
                $json[] = $storage->read($name);
            } catch (\JsonException $e) {
                error_log("Broken JSON file: \"$name.json\"");
            }
        }

        $response = new Response();
        $response->sendResponse(200, json_encode($json));
        return $response;
    }
}