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
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $password_hash = null;
    public ?string $role = null;
    public ?string $created_at = null;

    /**
     * Get the user's role.
     *
     * @return string|null Role name (e.g. 'admin', 'teacher', 'student') or null if not set.
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

}
