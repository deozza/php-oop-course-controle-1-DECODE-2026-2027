<?php

namespace Lib\Http;

class Request {
    private readonly string $uri;
    private readonly string $method;
    private readonly array $headers;
    private readonly string $body;

    public function __construct()
    {
        // On garde uniquement le chemin, sans la query string (?a=b)
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = getallheaders();
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

    /**
     * Récupère un header sans tenir compte de la casse (Content-Type == content-type).
     */
    public function getHeader(string $name): ?string
    {
        foreach($this->headers as $key => $value) {
            if(strtolower($key) === strtolower($name)) {
                return $value;
            }
        }

        return null;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
