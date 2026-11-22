<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Lib\Http\{Router, Request};

$router = new Router();
$request = new Request();
$response = $router->main($request);

http_response_code($response->statusCode);
echo $response->content;
