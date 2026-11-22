<?php

namespace Lib\Controllers;

use Lib\Http\{Response, Request};

abstract class AbstractController
{
    public abstract function process(Request $request): Response;
}

?>