<?php

namespace App\Controllers;

use App\Forms\ContactForm;
use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Services\ContactService;

class PostContactController extends AbstractController {

    // Crée et enregistre un nouveau formulaire de contact
    public function process(Request $request): Response
    {
        if ($request->headerHasValue('Content-Type', 'application/json') === false) {
            return new Response(
                json_encode(['error' => 'Invalid Content-Type header, application/json required']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $payload = json_decode($request->getPayload(), true);

        $contactForm = new ContactForm();
        if ($contactForm->checkPayloadIsValid($payload) === false) {
            return new Response(
                json_encode(['error' => 'Invalid payload']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $contactService = new ContactService();
        $contact = $contactService->buildContact($payload);
        $contactService->saveContact($contact);

        return new Response(
            json_encode(['file' => $contact->getId()]),
            201,
            ['Content-Type' => 'application/json']
        );
    }
}

