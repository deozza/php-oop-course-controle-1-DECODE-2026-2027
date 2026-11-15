<?php

namespace App\Controllers;

use App\Forms\ContactForm;
use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Services\ContactService;

class PatchContactController extends AbstractController {

    // Met à jour un formulaire de contact existant
    public function process(Request $request): Response
    {
        if ($request->headerHasValue('Content-Type', 'application/json') === false) {
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
        if ($contactService->findContactFilePath((string) $filename) === null) {
            return new Response(
                json_encode(['error' => 'Contact not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $payload = json_decode($request->getPayload(), true);

        $contactForm = new ContactForm();
        if ($contactForm->checkUpdatePayloadIsValid($payload) === false) {
            return new Response(
                json_encode(['error' => 'Invalid payload']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $updatedContact = $contactService->updateContact((string) $filename, $payload);
        if ($updatedContact === null) {
            return new Response(
                json_encode(['error' => 'Contact not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(
            json_encode($updatedContact, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}

