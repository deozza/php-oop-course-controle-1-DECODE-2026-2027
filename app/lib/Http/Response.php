<?php

namespace Lib\Http;

class Response {
    public int $statusCode = 200;
    public string $content = '';
    public array $headers = [];

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
