<?php

namespace Lib\Http;

class Request {
    private readonly string $uri;
    private readonly string $method;
    private readonly array $headers;

    public function __construct()
    {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = getallheaders();
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
