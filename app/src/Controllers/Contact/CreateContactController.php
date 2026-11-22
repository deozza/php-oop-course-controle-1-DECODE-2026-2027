<?php

namespace App\Controllers\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Validator\ContactValidator;
use Lib\Controllers\AbstractJsonController;
use Lib\Http\JsonResponse;
use Lib\Http\Request;

class CreateContactController extends AbstractJsonController {
    protected function handle(Request $request): JsonResponse
    {
        $values = (new ContactValidator())->validateCreation($this->getJsonBody($request));

        $contact = Contact::create($values['email'], $values['subject'], $values['message']);
        $filename = (new ContactRepository())->create($contact);

        return new JsonResponse(['file' => $filename], 201);
    }
}
