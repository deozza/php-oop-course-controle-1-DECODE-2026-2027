<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Services\ContactService;

class GetContactsController extends AbstractController {

    // Retourne la liste de tous les formulaires de contact
    public function process(Request $request): Response
    {
        if ($request->hasHeader('Content-Type') && $request->headerHasValue('Content-Type', 'application/json') === false) {
            return new Response(
                json_encode(['error' => 'Invalid Content-Type header, application/json required']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $queryFile = $_GET['file'] ?? ($_GET['filename'] ?? null);
        if ($queryFile !== null && trim((string) $queryFile) !== '') {
            $getContactController = new GetContactController();
            return $getContactController->process($request);
        }

        $contactService = new ContactService();
        $contacts = $contactService->getAllContacts();

        return new Response(
            json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
