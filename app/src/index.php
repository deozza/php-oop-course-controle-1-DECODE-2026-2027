<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\lib\Http\Router;
use App\lib\Http\Request;

$request = new Request();

$router = new Router();
$response = $router->main($request);

http_response_code($response->statusCode);
header('Content-Type: application/json');
echo $response->content;
