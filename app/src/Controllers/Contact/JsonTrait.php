<?php

namespace App\Controllers\Contact;

use App\lib\Http\Request;
use App\lib\Http\Response;

trait JsonTrait
{
    protected function readJson(Request $request): array|false
    {
        $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        if (!str_contains($headers['content-type'] ?? '', 'application/json')) {
            return false;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : false;
    }

    protected function json(int $status, mixed $data = null): Response
    {
        $response = new Response();
        $response->statusCode = $status;
        $response->content = $data === null
            ? ''
            : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $response;
    }

    protected function getFilename(Request $request): ?string
    {
        if (isset($_GET['file']) && is_string($_GET['file']) && $_GET['file'] !== '') {
            return $_GET['file'];
        }

        return null;
    }
}
