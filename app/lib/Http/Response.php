<?php

namespace Lib\Http;

class Response {
    public int $statusCode;
    public string $content = '';
    public array $headers = [];

    public static function json(int $statusCode, mixed $data = null): Response
    {
        $response = new Response();
        $response->statusCode = $statusCode;
        $response->headers['Content-Type'] = 'application/json';

        if ($data !== null) {
            $response->content = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $response;
    }

    public static function error(int $statusCode, string $message): Response
    {
        return self::json($statusCode, ['error' => $message]);
    }
}
