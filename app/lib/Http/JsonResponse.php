<?php

namespace Lib\Http;

class JsonResponse extends Response {
    public function __construct(mixed $data = null, int $statusCode = 200)
    {
        $this->statusCode = $statusCode;

        if ($statusCode === 204 || $data === null) {
            $this->content = '';
            return;
        }

        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->content = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    public static function error(int $statusCode, string $message, array $details = []): self
    {
        $data = ['error' => $message];

        if ($details !== []) {
            $data['details'] = $details;
        }

        return new self($data, $statusCode);
    }
}
