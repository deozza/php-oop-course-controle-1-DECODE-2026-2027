<?php

namespace App\Entities;

class Contact implements \JsonSerializable {
    public const CONTACT_ID_FORMAT = '%s_%s.json';
    public const CONTACT_FILENAME_FORMAT = '%d_%s.json';

    public string $email;
    public string $subject;
    public string $message;
    private int $dateOfCreation;
    private int $dateOfLastUpdate;

    public function __construct(?int $dateOfCreation = null, ?int $dateOfLastUpdate = null)
    {
        $now = time();
        $this->dateOfCreation = $dateOfCreation ?? $now;
        $this->dateOfLastUpdate = $dateOfLastUpdate ?? $this->dateOfCreation;
    }

    public function getDateOfCreation(): int
    {
        return $this->dateOfCreation;
    }

    public function setDateOfCreation(int $dateOfCreation): void
    {
        $this->dateOfCreation = $dateOfCreation;
    }

    public function getDateOfLastUpdate(): int
    {
        return $this->dateOfLastUpdate;
    }

    public function setDateOfLastUpdate(int $dateOfLastUpdate): void
    {
        $this->dateOfLastUpdate = $dateOfLastUpdate;
    }

    // Génère le nom du fichier formaté pour la réponse JSON
    public function getId(): string
    {
        return sprintf(self::CONTACT_ID_FORMAT, date('Y-m-d_H-i-s', $this->dateOfCreation), $this->email);
    }

    // Génère le nom du fichier stocké sur le disque
    public function getFilename(): string
    {
        return sprintf(self::CONTACT_FILENAME_FORMAT, $this->dateOfCreation, $this->email);
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'dateOfCreation' => $this->dateOfCreation,
            'dateOfLastUpdate' => $this->dateOfLastUpdate,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

