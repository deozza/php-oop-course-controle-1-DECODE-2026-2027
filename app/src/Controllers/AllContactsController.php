<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class AllContactsController extends AbstractController {
    public function process(Request $request): Response {
        $dir = self::CONTACTS_DIR;
        $allContacts = [];

        if (is_dir($dir)) {
            $contacts = scandir($dir);

            foreach ($contacts as $contact) {
                if ($contact === '.' || $contact === '..') {
                    continue;
                }
            
                $allContacts[] = json_decode(file_get_contents($dir . $contact), true);
            }
        }

        $response = new Response();

        $response->statusCode = 200;
        $response->content = json_encode($allContacts);

        return $response;
    }
}