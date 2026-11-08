<?php

namespace App\Services;

use App\Entities\Contact;
use App\Forms\ContactForm;

class ContactService {

    public const CONTACT_DIR = __DIR__ . '/../../var/contacts/';

    public function __construct()
    {
        if (!is_dir(self::CONTACT_DIR)) {
            mkdir(self::CONTACT_DIR, 0777, true);
        }
    }

    public function buildContact(array $payload): Contact
    {
        $contact = new Contact();
        $contact->email = $payload['email'];
        $contact->subject = $payload['subject'];
        $contact->message = $payload['message'];

        return $contact;
    }

    public function saveContact(Contact $contact): void
    {
        $contactForm = new ContactForm();
        $data = $contact->toArray();

        if ($contactForm->checkContactOutputIsValid($data) === false) {
            throw new \RuntimeException('Invalid contact output data');
        }

        $filename = $contact->getFilename();
        file_put_contents(
            self::CONTACT_DIR . $filename,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
