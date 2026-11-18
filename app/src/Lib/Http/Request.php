<?php

namespace App\Lib\Http;

class Request {
    private readonly string $uri;
    private readonly string $method;
    private readonly array $headers;
    private readonly string $payload;
    private array $pathParams = [];

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->headers = function_exists('getallheaders') ? (getallheaders() ?: []) : $this->extractHeadersFromServer();
        $this->payload = file_get_contents('php://input') ?: '';
    }

    private function extractHeadersFromServer(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            } elseif (in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
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

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function hasHeader(string $key): bool
    {
        foreach ($this->headers as $headerKey => $headerValue) {
            if (strcasecmp($headerKey, $key) === 0) {
                return true;
            }
        }
        return false;
    }

    public function getHeader(string $key): string
    {
        foreach ($this->headers as $headerKey => $headerValue) {
            if (strcasecmp($headerKey, $key) === 0) {
                return (string) $headerValue;
            }
        }

        throw new \Exception("header $key not found");
    }

    // Vérifie si un header possède la valeur attendue
    public function headerHasValue(string $key, string $value): bool
    {
        if ($this->hasHeader($key) === false) {
            return false;
        }

        $header = strtolower(trim($this->getHeader($key)));
        $expected = strtolower(trim($value));

        return $header === $expected || str_starts_with($header, $expected . ';');
    }

    public function setPathParams(array $params): void
    {
        $this->pathParams = $params;
    }

    public function getPathParams(): array
    {
        return $this->pathParams;
    }

    public function getPathParam(string $key): ?string
    {
        return $this->pathParams[$key] ?? null;
    }
}

