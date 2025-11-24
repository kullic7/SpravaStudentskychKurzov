<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Class User
 *
 * Model for the `users` table.
 * Columns (DB / property):
 * - id -> id
 * - first_name -> firstName
 * - last_name -> lastName
 * - email -> email
 * - password_hash -> passwordHash
 * - role -> role
 * - created_at -> createdAt
 */
class User extends Model
{
    // Optional: explicitly set table name (conventions would resolve this automatically)
    protected static ?string $tableName = 'users';

    // Model properties (camelCase) — Framework will map snake_case DB columns to these names.
    public ?int $id = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
    public ?string $passwordHash = null;
    public ?string $role = null;
    public ?string $createdAt = null;

    /**
     * Convenience: set password (hashes using PHP's password_hash)
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Convenience: verify password against the stored hash
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        if ($this->passwordHash === null) {
            return false;
        }
        return password_verify($password, $this->passwordHash);
    }
}

