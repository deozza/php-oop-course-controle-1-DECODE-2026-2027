<?php

namespace App\Controllers\Contact;

use App\Contact\ContactStorage;
use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class GetContactController extends AbstractController
{
    use JsonTrait;

    public function process(Request $request): Response
    {
        $storage = new ContactStorage();
        $file = $this->getFilename($request);

        if ($file === null) {
            return $this->json(200, $storage->findAll());
        }

        $contact = $storage->find($file);
        if ($contact === null) {
            return $this->json(404, ['error' => 'Contact not found']);
        }

        return $this->json(200, $contact);
    }
}
