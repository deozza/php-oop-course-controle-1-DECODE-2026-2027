<?php

namespace App\Entity;

class Contact {
    public function __construct(
        private string $email,
        private string $subject,
        private string $message,
        private readonly int $dateOfCreation,
        private int $dateOfLastUpdate,
    ) {
    }

    public static function create(string $email, string $subject, string $message): self
    {
        $now = time();

        return new self($email, $subject, $message, $now, $now);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDateOfCreation(): int
    {
        return $this->dateOfCreation;
    }

    public function getDateOfLastUpdate(): int
    {
        return $this->dateOfLastUpdate;
    }

    public function update(array $values): void
    {
        $this->email = $values['email'] ?? $this->email;
        $this->subject = $values['subject'] ?? $this->subject;
        $this->message = $values['message'] ?? $this->message;
        $this->dateOfLastUpdate = time();
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

    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'],
            $data['subject'],
            $data['message'],
            $data['dateOfCreation'],
            $data['dateOfLastUpdate'],
        );
    }
}
