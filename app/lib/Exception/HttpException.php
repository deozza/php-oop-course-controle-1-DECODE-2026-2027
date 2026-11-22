<?php

namespace Lib\Exception;

class HttpException extends \RuntimeException {
    public function __construct(
        public readonly int $statusCode,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message, $statusCode);
    }
}
