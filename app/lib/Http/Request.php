<?php

namespace Lib\Http;

class Request {
    private readonly string $uri;
    private readonly string $method;
    private readonly array $headers;
    private readonly string $body;

    private array $routeParams = [];

    public function __construct()
    {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->headers = getallheaders() ?: [];
        $this->body = file_get_contents('php://input') ?: '';
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

    public function getHeader(string $name): ?string
    {
        foreach ($this->headers as $headerName => $value) {
            if (strcasecmp($headerName, $name) === 0) {
                return $value;
            }
        }

        return null;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    public function getRouteParam(string $name): ?string
    {
        return $this->routeParams[$name] ?? null;
    }
}
