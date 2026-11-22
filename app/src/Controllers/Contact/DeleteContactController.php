<?php

namespace App\Controllers\Contact;

use App\Repository\ContactRepository;
use Lib\Controllers\AbstractJsonController;
use Lib\Exception\HttpException;
use Lib\Http\JsonResponse;
use Lib\Http\Request;

class DeleteContactController extends AbstractJsonController {
    protected function handle(Request $request): JsonResponse
    {
        $deleted = (new ContactRepository())->delete($request->getRouteParam('filename') ?? '');

        if ($deleted === false) {
            throw new HttpException(404, 'contact form not found');
        }

        return new JsonResponse(null, 204);
    }
}
