<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class UpdateContact extends AbstractController
{
    public function process(Request $request): Response
    {
        $uri = basename($request->getUri());
        $pathToFileContact = __DIR__ . '/../../var/contacts/' . $uri;

        if (!file_exists($pathToFileContact)) {
            return $this->httpResponse("Le fichier n'existe pas", 404);
        }

        $dataJSON = file_get_contents('php://input');
        $data = json_decode($dataJSON, true);
        if (!is_array($data)) {
            return $this->httpResponse('Le format JSON est invalide', 400);
        }

        if (isset($data[''])) {
            return $this->httpResponse("Vos données sont vides", 400);
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->httpResponse("Le mail n'est pas valide", 400);
        }

        $obligatoryKeys = ['email', 'subject', 'message'];
        $nameKeys = array_keys($data);

        foreach ($nameKeys as $key) {
            if (!in_array($key, $obligatoryKeys)) {
                return $this->httpResponse("Le champ '$key' n'existe pas dans vos données", 400);
            }
        }
        $contentFile = json_decode(file_get_contents($pathToFileContact), true);

        foreach ($nameKeys as $key) {
            $contentFile[$key] = $data[$key];
        }
        $contentFile['dateOfLastUpdate'] = time();

        $newDataJson = json_encode($contentFile, JSON_PRETTY_PRINT);

        file_put_contents($pathToFileContact, $newDataJson);

        return $this->httpResponse($newDataJson, 200);
    }
}
