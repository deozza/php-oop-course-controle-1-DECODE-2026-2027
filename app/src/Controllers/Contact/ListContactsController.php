<?php

namespace App\Controllers\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Lib\Controllers\AbstractJsonController;
use Lib\Http\JsonResponse;
use Lib\Http\Request;

class ListContactsController extends AbstractJsonController {
    protected function handle(Request $request): JsonResponse
    {
        $contacts = (new ContactRepository())->findAll();

        return new JsonResponse(array_map(fn (Contact $contact) => $contact->toArray(), $contacts), 200);
    }
}
