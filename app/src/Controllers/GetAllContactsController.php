<?php

namespace App\Controllers;

use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class GetAllContactsController extends AbstractController
{

    private const string CONTACTS_PATH = __DIR__ . '/../../var/contacts/';

    public function process(Request $request): Response
    {
        $contacts = [];
        $files = glob(self::CONTACTS_PATH . '*.json');

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true);

            if (is_array($content) === true) {
                $contacts[] = $content;
            }
        }

        $response = new Response();
        $response->statusCode = 200;
        $response->headers['Content-Type'] = 'application/json';
        $response->content = json_encode($contacts);

        return $response;
    }
}
