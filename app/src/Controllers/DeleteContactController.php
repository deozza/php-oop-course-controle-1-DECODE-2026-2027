<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Services\ContactService;

class DeleteContactController extends AbstractController {

    // Supprime un formulaire de contact existant
    public function process(Request $request): Response
    {
        if ($request->hasHeader('Content-Type') && $request->headerHasValue('Content-Type', 'application/json') === false) {
            return new Response(
                json_encode(['error' => 'Invalid Content-Type header, application/json required']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $filename = $request->getPathParam('filename') ?? ($_GET['file'] ?? ($_GET['filename'] ?? null));
        if ($filename === null || trim((string) $filename) === '') {
            return new Response(
                json_encode(['error' => 'Contact not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $contactService = new ContactService();
        $deleted = $contactService->deleteContact((string) $filename);

        if ($deleted === false) {
            return new Response(
                json_encode(['error' => 'Contact not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response('', 204, ['Content-Type' => 'application/json']);
    }
}

