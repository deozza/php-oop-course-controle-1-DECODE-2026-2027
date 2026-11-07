<?php

namespace App\Lib\Http;

class Response {
    public int $statusCode;
    public string $content;
    public array $headers;

    public function __construct(
        string $content = '',
        int $statusCode = 200,
        array $headers = ['Content-Type' => 'application/json']
    ) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeadersAsString(): string
    {
        $headersAsString = '';
        foreach ($this->getHeaders() as $headerName => $headerValue) {
            $headersAsString .= "$headerName: $headerValue\n";
        }

        return $headersAsString;
    }
}
