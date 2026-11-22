<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Lib\Http\Router;
use Lib\Http\Request;

$request = new Request();

$router = new Router();
$router->main($request);


?>
