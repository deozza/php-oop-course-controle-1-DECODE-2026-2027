<?php

namespace App\Controllers;

use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class DeleteContactController extends AbstractController
{

    private const string CONTACTS_PATH = __DIR__ . '/../../var/contacts/';

    public function process(Request $request): Response
    {
        $params = $request->getQueryParams();
        $response = new Response();

        if (isset($params['file']) === false || is_string($params['file']) === false) {
            $response->statusCode = 400;
            $response->headers['Content-Type'] = 'application/json';
            $response->content = json_encode(['error' => 'file parameter is required']);

            return $response;
        }

        $fileName = basename($params['file']);
        $path = self::CONTACTS_PATH . $fileName;

        if (str_ends_with($fileName, '.json') === false || file_exists($path) === false) {
            $response->statusCode = 404;
            $response->headers['Content-Type'] = 'application/json';
            $response->content = json_encode(['error' => 'contact not found']);

            return $response;
        }

        unlink($path);

        $response->statusCode = 204;
        $response->content = '';

        return $response;
    }
}
