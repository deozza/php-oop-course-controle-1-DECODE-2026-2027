<?php

namespace App\Controllers\Contact;

use App\Repository\ContactRepository;
use Lib\Controllers\AbstractJsonController;
use Lib\Exception\HttpException;
use Lib\Http\JsonResponse;
use Lib\Http\Request;

class GetContactController extends AbstractJsonController {
    protected function handle(Request $request): JsonResponse
    {
        $contact = (new ContactRepository())->find($request->getRouteParam('filename') ?? '');

        if ($contact === null) {
            throw new HttpException(404, 'contact form not found');
        }

        return new JsonResponse($contact->toArray(), 200);
    }
}
