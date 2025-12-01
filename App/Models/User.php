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
    protected static array $columnsMap = [
        'first_name' => 'firstName',
        'last_name'  => 'lastName',
        'password_hash' => 'passwordHash',
        'created_at' => 'createdAt',
    ];

    // Model properties (camelCase) — Framework will map snake_case DB columns to these names.
    public ?int $id = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
    public ?string $passwordHash = null;
    public ?string $role = null;
    public ?string $createdAt = null;

    /**
     * Get the user's role.
     *
     * @return string|null Role name (e.g. 'admin', 'teacher', 'student') or null if not set.
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    // -------------------- convenience helpers --------------------

    /**
     * Find user by primary id (wrapper around Model::getOne)
     * @param int $id
     * @return static|null
     */
    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }

    /**
     * Find user by email
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email): ?static
    {
        $items = static::getAll('email = ?', [$email], null, 1);
        return $items[0] ?? null;
    }

    /**
     * Update profile fields from an associative array and save. Returns array of errors (empty on success).
     * Expected keys: firstName, lastName, email, password, passwordConfirm
     * @param array $data
     * @return array<string>
     */
    public function updateProfile(array $data): array
    {
        $errors = [];

        $firstName = isset($data['firstName']) ? trim((string)$data['firstName']) : null;
        $lastName = isset($data['lastName']) ? trim((string)$data['lastName']) : null;
        $email = isset($data['email']) ? trim((string)$data['email']) : null;
        $password = $data['password'] ?? null;
        $passwordConfirm = $data['passwordConfirm'] ?? null;

        if ($email === null || $email === '') {
            $errors[] = 'Email je povinný.';
        }
        if ($firstName === null || $firstName === '') {
            $errors[] = 'Meno je povinné.';
        }
        if ($lastName === null || $lastName === '') {
            $errors[] = 'Priezvisko je povinné.';
        }

        if ($password !== null && $password !== '') {
            if ($password !== $passwordConfirm) {
                $errors[] = 'Heslá sa nezhodujú.';
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        // apply
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;

        if ($password !== null && $password !== '') {
            $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->save();

        return [];
    }

}
