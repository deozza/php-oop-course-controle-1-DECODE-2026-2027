<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class SingleContactController extends AbstractController {
    public function process(Request $request): Response {
        $dir = self::CONTACTS_DIR;
        $fileName = basename($request->getUri());

        if (is_dir($dir)) {
            $contacts = scandir($dir);

            foreach ($contacts as $contact) {
                if ($contact === '.' || $contact === '..') {
                    continue;
                } else if ($contact === $fileName) {
                    $fileContent = json_decode(file_get_contents($dir . $contact), true);

                    $response = new Response();
                    $response->statusCode = 200;
                    $response->content = json_encode($fileContent);

                    return $response;
                }
            }
        }

        $response = new Response();
        $response->statusCode = 404;
        $response->content = json_encode(['error' => 'Contact not found']);
        return $response;
    }
}