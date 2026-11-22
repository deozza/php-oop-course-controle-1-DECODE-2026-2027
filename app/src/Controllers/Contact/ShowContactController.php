<?php

namespace App\Controllers\Contact;

use App\Services\ContactRepository;
use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

class ShowContactController extends AbstractController {
    public function process(Request $request): Response {
        $contact = (new ContactRepository())->find($request->getParam('filename'));

        if ($contact === null) {
            return Response::error(404, 'Contact not found');
        }

        return Response::json(200, $contact);
    }
}
