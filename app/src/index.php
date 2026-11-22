<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Router;
use App\Http\Request;

$request = new Request();

$router = new Router();
$response = $router->main($request);

echo $response->content;
exit();
