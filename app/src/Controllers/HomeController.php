<?php

namespace App\Controllers;

use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

class HomeController extends AbstractController {
    public function process(Request $request): Response {
        $response = new Response();
        $response->statusCode = 200;
        $response->content = 'success';

        return $response;
    }
}
