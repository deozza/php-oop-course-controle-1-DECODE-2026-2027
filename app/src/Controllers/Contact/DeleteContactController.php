<?php

namespace App\Controllers\Contact;

use App\Services\ContactRepository;
use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

class DeleteContactController extends AbstractController {
    public function process(Request $request): Response {
        if ((new ContactRepository())->delete($request->getParam('filename')) === false) {
            return Response::error(404, 'Contact not found');
        }

        return Response::json(204);
    }
}
