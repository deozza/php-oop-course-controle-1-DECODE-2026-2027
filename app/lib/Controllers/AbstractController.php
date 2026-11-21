<?php

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

abstract class AbstractController {
    protected const CONTACTS_DIR = __DIR__ . '/../../var/contacts/';

    public abstract function process(Request $request): Response;
}