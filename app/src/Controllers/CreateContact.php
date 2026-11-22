<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class CreateContact extends AbstractController
{
    public function process(Request $request): Response
    {
        if ($request->getMethod() !== "POST") {
            return $this->httpResponse("La méthode reçu n'est pas la méthode attendue", 405);
        }

        $dataJSON = file_get_contents('php://input');
        $data = json_decode($dataJSON, true);
        if (!is_array($data)) {
            return $this->httpResponse('Le format JSON est invalide', 400);
        }

        $obligatoryKeys = ['email', 'subject', 'message'];
        $nameKeys = array_keys($data);

        if ($obligatoryKeys !== $nameKeys) {
            return $this->httpResponse("Tout les champs n'ont pas été rempli", 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->httpResponse("Le mail n'est pas valide", 400);
        }

        $newPathForContact = __DIR__ . '/../../var/contacts';
        if (!is_dir($newPathForContact)) {
            mkdir($newPathForContact, 0777, true);
        }

        $timestamp = time();

        $nameFile = date('Y-m-d_H-i-s', $timestamp) . "_" . $data["email"] . ".json";

        $newData = ['email' => $data['email'], 'subject' => $data['subject'], 'message' => $data['message'], 'dateOfCreation' => $timestamp, 'dateOfLastUpdate' => $timestamp];

        $finalPath = $newPathForContact . "/" . $nameFile;

        file_put_contents($finalPath, json_encode($newData, JSON_ERROR_DEPTH));



        return $this->httpResponse(json_encode(['file' => $nameFile]), 201);
    }
}
