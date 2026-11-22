<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Lib\Http\Router;
use Lib\Http\Request;
use Lib\Http\Response;

try {
    $request = new Request();

    $router = new Router();
    $response = $router->main($request);
} catch(\Throwable $e) {
    $response = new Response();
    $response->statusCode = 500;
    $response->content = 'internal server error';
}

$response->send();
