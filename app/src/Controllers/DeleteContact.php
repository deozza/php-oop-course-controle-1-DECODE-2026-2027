<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class DeleteContact extends AbstractController
{
    public function process(Request $request): Response
    {
        $uri = basename($request->getUri());
        $pathToFileContact = __DIR__ . '/../../var/contacts/' . $uri;

        if (!file_exists($pathToFileContact)) {
            return $this->httpResponse("Le fichier n'existe pas", 404);
        }

        unlink($pathToFileContact);
        return $this->httpResponse("", 204);
    }
}
