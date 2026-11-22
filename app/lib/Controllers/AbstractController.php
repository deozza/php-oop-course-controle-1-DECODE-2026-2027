<?php

namespace Lib\Controllers;

use Lib\Http\Response;
use Lib\Http\Request;

abstract class AbstractController {
    public abstract function process(Request $request): Response;
}

?>

