<?php

namespace App\Controllers\Contact;

use App\Services\ContactRepository;
use App\Services\ContactValidator;
use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

class UpdateContactController extends AbstractController {
    public function process(Request $request): Response {
        $repository = new ContactRepository();
        $filename = $request->getParam('filename');
        $contact = $repository->find($filename);

        if ($contact === null) {
            return Response::error(404, 'Contact not found');
        }

        if ($request->isJson() === false) {
            return Response::error(415, 'Content-Type must be application/json');
        }

        $data = json_decode($request->getBody(), true);

        $error = (new ContactValidator())->validate($data, true);
        if ($error !== null) {
            return Response::error(400, $error);
        }

        $contact = array_merge($contact, $data);
        $contact['dateOfLastUpdate'] = time();

                if ($repository->save($filename, $contact) === false) {
            return Response::error(500, 'Unable to save contact');
        }

        return Response::json(200, $contact);
    }
}
