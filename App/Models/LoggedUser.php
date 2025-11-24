<?php

namespace App\Security;

use Framework\Core\IIdentity;

class LoggedUser implements IIdentity
{
    public function __construct(
        private int $id,
        private string $email,
        private string $firstName,
        private string $lastName,
        private string $role
    ) {}

    public function getId(): int {
        return $this->id;
    }

    public function getRoles(): array {
        return [$this->role];
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRole(): string {
        return $this->role;
    }
}
