<?php

namespace Lib\Http;

class Request {
    private readonly string $uri;
    private readonly string $method;
    private readonly array $headers;
    private array $params = [];

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = array_change_key_case(getallheaders(), CASE_LOWER);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return parse_url($this->uri, PHP_URL_PATH) ?? '/';
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
        return $this->headers[strtolower($name)] ?? null;
    }

    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type') ?? '';

        return str_starts_with(strtolower(trim($contentType)), 'application/json');
    }

    public function getBody(): string
    {
        return file_get_contents('php://input');
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParam(string $name): ?string
    {
        return $this->params[$name] ?? null;
    }
}
