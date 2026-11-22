<?php

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

abstract class AbstractController
{
    public abstract function process(Request $request): Response;
    protected function httpResponse(string $content, int $statusCode): Response
    {
        $response = new Response();
        $response->statusCode = $statusCode;
        $response->content = $content;
        return $response;
    }
}
