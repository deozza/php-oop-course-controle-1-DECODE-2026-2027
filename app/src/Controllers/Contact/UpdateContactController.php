<?php

namespace App\Controllers\Contact;

use App\Repository\ContactRepository;
use App\Validator\ContactValidator;
use Lib\Controllers\AbstractJsonController;
use Lib\Exception\HttpException;
use Lib\Http\JsonResponse;
use Lib\Http\Request;

class UpdateContactController extends AbstractJsonController {
    protected function handle(Request $request): JsonResponse
    {
        $filename = $request->getRouteParam('filename') ?? '';
        $repository = new ContactRepository();

        $contact = $repository->find($filename);

        if ($contact === null) {
            throw new HttpException(404, 'contact form not found');
        }

        $values = (new ContactValidator())->validateUpdate($this->getJsonBody($request));

        $contact->update($values);
        $repository->update($filename, $contact);

        return new JsonResponse($contact->toArray(), 200);
    }
}
