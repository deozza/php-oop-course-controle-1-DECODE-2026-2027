<?php

namespace App\Controllers\Contact;

use App\Services\ContactRepository;
use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

class ListContactsController extends AbstractController {
    public function process(Request $request): Response {
        return Response::json(200, (new ContactRepository())->findAll());
    }
}
