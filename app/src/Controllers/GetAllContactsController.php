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
        $params = $request->getQueryParams();

        if (isset($params['file']) === true) {
            return $this->getOneContact($params['file']);
        }

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

    private function getOneContact(string $fileName): Response
    {
        $response = new Response();
        $response->headers['Content-Type'] = 'application/json';

        $fileName = basename($fileName);
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
