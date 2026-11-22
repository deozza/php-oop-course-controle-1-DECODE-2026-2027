<?php

namespace App\Controllers;

use App\lib\Http\Request;
use App\lib\Http\Response;

class HomeController extends AbstractController
{
    public function process(Request $request): Response
    {
        $response = new Response();
        $response->statusCode = 200;
        $response->content = 'success';

        return $response;
    }
}
