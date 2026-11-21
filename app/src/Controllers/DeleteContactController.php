<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class DeleteContactController extends AbstractController {
    public function process(Request $request): Response {
        $dir = self::CONTACTS_DIR;
        $fileName = basename($request->getUri());

        $headers = $request->getHeaders();

        if (is_dir($dir)) {
            $contacts = scandir($dir);

            foreach ($contacts as $contact) {

                if ($contact === '.' || $contact === '..') {
                    continue;
                } else if ($contact === $fileName) {

                $response = new Response();

                    if($headers['Content-Type'] !== 'application/json') {
                        $response->statusCode = 400;
                        $response->content = json_encode(['error' => 'Invalid Content-Type. Expected application/json']);
                        return $response;
                    } else {                        
                        $filePath = $dir . $contact;

                        unlink($filePath);
                    
                        $response->statusCode = 204;
                        $response->content = '';

                        return $response;
                    }
                }
            }
        }

        $response = new Response();
        $response->statusCode = 404;
        $response->content = json_encode(['error' => 'Contact not found']);
        return $response;
    }
}