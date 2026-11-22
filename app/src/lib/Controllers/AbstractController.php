<?php

namespace App\lib\Controllers;

use App\lib\Http\Request;
use App\lib\Http\Response;

abstract class AbstractController
{
    public abstract function process(Request $request): Response;
}
