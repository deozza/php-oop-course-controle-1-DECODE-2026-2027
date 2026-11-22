<?php

namespace App\Controllers;

use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class PatchContactController extends AbstractController
{

    private const string CONTACTS_PATH = __DIR__ . '/../../var/contacts/';
    private const array ALLOWED_KEYS = ['email', 'subject', 'message'];

    public function process(Request $request): Response
    {
        $params = $request->getQueryParams();

        if (isset($params['file']) === false || is_string($params['file']) === false) {
            return $this->createResponse(400, ['error' => 'file parameter is required']);
        }

        $fileName = basename($params['file']);
        $path = self::CONTACTS_PATH . $fileName;

        if (str_ends_with($fileName, '.json') === false || file_exists($path) === false) {
            return $this->createResponse(404, ['error' => 'contact not found']);
        }

        $headers = $request->getHeaders();

        if (isset($headers['Content-Type']) === false || $headers['Content-Type'] !== 'application/json') {
            return $this->createResponse(400, ['error' => 'Content-Type must be application/json']);
        }

        $data = json_decode($request->getBody(), true);

        if (is_array($data) === false || count($data) === 0) {
            return $this->createResponse(400, ['error' => 'Invalid JSON']);
        }

        foreach ($data as $key => $value) {
            if (in_array($key, self::ALLOWED_KEYS, true) === false) {
                return $this->createResponse(400, ['error' => 'Only email, subject and message can be updated']);
            }

            if (is_string($value) === false || trim($value) === '') {
                return $this->createResponse(400, ['error' => $key . ' must be a non empty string']);
            }
        }

        if (isset($data['email']) === true && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return $this->createResponse(400, ['error' => 'Invalid email']);
        }

        $contact = json_decode(file_get_contents($path), true);

        foreach ($data as $key => $value) {
            $contact[$key] = $value;
        }
        $contact['dateOfLastUpdate'] = time();

        file_put_contents($path, json_encode($contact));

        return $this->createResponse(200, $contact);
    }

    private function createResponse(int $statusCode, array $content): Response
    {
        $response = new Response();
        $response->statusCode = $statusCode;
        $response->headers['Content-Type'] = 'application/json';
        $response->content = json_encode($content);

        return $response;
    }
}
