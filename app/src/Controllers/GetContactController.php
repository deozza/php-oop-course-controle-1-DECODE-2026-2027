<?php

namespace App\Controllers;

use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class GetContactController extends AbstractController
{

    private const string CONTACTS_PATH = __DIR__ . '/../../var/contacts/';

    public function process(Request $request): Response
    {
        $response = new Response();
        $response->headers['Content-Type'] = 'application/json';

        $fileName = basename(urldecode($request->getUri()));
        $path = self::CONTACTS_PATH . $fileName;

        if (str_ends_with($fileName, '.json') === false || file_exists($path) === false) {
            $response->statusCode = 404;
            $response->content = json_encode(['error' => 'contact not found']);

            return $response;
        }

        $response->statusCode = 200;
        $response->content = file_get_contents($path);

        return $response;
    }
}
