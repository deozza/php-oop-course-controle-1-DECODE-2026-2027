<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Lib\Http\Router;
use App\Lib\Http\Request;

$request = new Request();

$router = new Router();
$response = $router->main($request);

foreach ($response->getHeaders() as $name => $value) {
    header("$name: $value");
}

http_response_code($response->getStatus());
echo $response->getContent();
