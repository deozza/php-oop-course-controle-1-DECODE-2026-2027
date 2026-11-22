<?php

namespace App\Controllers\Contact;

use App\Services\ContactRepository;
use App\Services\ContactValidator;
use Lib\Controllers\AbstractController;
use Lib\Http\Request;
use Lib\Http\Response;

class CreateContactController extends AbstractController {
    public function process(Request $request): Response {
        if ($request->isJson() === false) {
            return Response::error(415, 'Content-Type must be application/json');
        }

        $data = json_decode($request->getBody(), true);

        $error = (new ContactValidator())->validate($data);
        if ($error !== null) {
            return Response::error(400, $error);
        }

        $now = time();
        $contact = [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $now,
            'dateOfLastUpdate' => $now,
        ];

        $filename = $now . '_' . $data['email'] . '.json';
        if ((new ContactRepository())->save($filename, $contact) === false) {
            return Response::error(500, 'Unable to save contact');
        }

        return Response::json(201, ['file' => $filename]);
    }
}
