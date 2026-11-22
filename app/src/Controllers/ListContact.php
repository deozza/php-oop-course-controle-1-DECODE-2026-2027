<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class ListContact extends AbstractController
{
    public function process(Request $request): Response
    {
        $newPathForContact = __DIR__ . '/../../var/contacts';
        $contacts = [];

        $allContactFile = scandir($newPathForContact);


        foreach ($allContactFile as $file) {

            if ($file != '.' && $file != '..' && preg_match('/\.json$/', $file) === 1) {
                $dataFile = file_get_contents($newPathForContact . "/" . $file, true);

                $contacts[] = json_decode($dataFile, true, JSON_THROW_ON_ERROR);
            }
        }
        return $this->httpResponse(json_encode($contacts, JSON_PRETTY_PRINT), 200);
    }
}
