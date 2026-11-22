<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Lib\Http\Router;
use Lib\Http\Request;

$request = new Request();

$router = new Router();
$response = $router->main($request);

http_response_code($response->statusCode);

foreach ($response->headers as $name => $value) {
    header($name . ': ' . $value);
}

echo $response->content;
