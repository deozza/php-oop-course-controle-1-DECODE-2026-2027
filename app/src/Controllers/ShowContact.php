<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class ShowContact extends AbstractController
{
    public function process(Request $request): Response
    {

        $uri = $request->getUri();
        $basenameUri = basename($uri);


        $newPathForVar = __DIR__ . '/../../var/contacts';
        $pathUri = $newPathForVar . "/" . $basenameUri;

        if (!file_exists($pathUri)) {
            return $this->httpResponse("Le fichier n'existe pas", 404);
        }

        return $this->httpResponse(file_get_contents($pathUri), 200);
    }
}
