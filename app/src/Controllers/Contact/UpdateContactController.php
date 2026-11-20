<?php

namespace App\Controllers\Contact;

use App\Contact\ContactStorage;
use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class UpdateContactController extends AbstractController
{
    use JsonTrait;

    public function process(Request $request): Response
    {
        $storage = new ContactStorage();
        $file = $this->getFilename($request);

        if ($file === null || $storage->find($file) === null) {
            return $this->json(404, ['error' => 'Contact not found']);
        }

        $data = $this->readJson($request);
        if ($data === false || !$storage->validate($data, true)) {
            return $this->json(400, ['error' => 'Invalid body']);
        }

        $updated = $storage->update($file, $data);
        if ($updated === null) {
            return $this->json(500, ['error' => 'Unable to update the contact']);
        }

        return $this->json(200, $updated);
    }
}
