<?php

namespace App\Http;

class Response
{
    public int $statusCode;
    public string $content;
    public array $headers = [
        'Content-Type' => 'application/json'
    ];
}
