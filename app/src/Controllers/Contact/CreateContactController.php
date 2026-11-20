<?php

namespace App\Controllers\Contact;

use App\Contact\ContactStorage;
use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class CreateContactController extends AbstractController
{
    use JsonTrait;

    public function process(Request $request): Response
    {
        $data = $this->readJson($request);
        $storage = new ContactStorage();

        if ($data === false || !$storage->validate($data)) {
            return $this->json(400, ['error' => 'Invalid body']);
        }

        $file = $storage->create($data);
        if ($file === null) {
            return $this->json(500, ['error' => 'Unable to save the contact']);
        }

        return $this->json(201, ['file' => $file]);
    }
}
