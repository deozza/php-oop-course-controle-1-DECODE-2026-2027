<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Router;
use App\Http\Request;

$request = new Request();

$router = new Router();
$response = $router->main($request);

http_response_code($response->statusCode);
header('Content-Type: application/json');

echo $response->content;

// Commande pour tester la feature d'ajout de contact (exercice 1) : Invoke-RestMethod -Uri "http://127.0.0.1:8181/contact" -Method Post -ContentType "application/json" -Body '{"email":"...","subject":"...","message":"..."}'
// Commande pour tester la feature de modification de contact (exercice 4) : Invoke-RestMethod -Uri "http://127.0.0.1:8181/contact/<filename>" -Method Patch -ContentType "application/json" -Body '{...}'
// Commande pour tester la feature de suppression de contact (exercice 5) : Invoke-RestMethod -Uri "http://127.0.0.1:8181/contact/<filename>" -Method Delete -ContentType "application/json"

?>