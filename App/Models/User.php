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

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): string {
        return $this->firstName . ' ' . $this->lastName;
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
     * Convenience: return all users
     * @return static[]
     */
    public static function getAllUsers(): array
    {
        return static::getAll();
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
     * Validate a name (first name or last name).
     * Accepts null/empty and returns appropriate error(s).
     * @param string|null $value
     * @param string $fieldName
     * @return string[] array of error messages
     */
    protected static function validateName(?string $value, string $fieldName): array
    {
        $errors = [];
        $val = $value === null ? '' : trim((string)$value);
        if ($val === '') {
            $errors[] = $fieldName . ' je povinné.';
            return $errors;
        }
        if (mb_strlen($val) > 20) {
            $errors[] = $fieldName . ' môže mať maximálne 20 znakov.';
        }
        // allow Unicode letters and digits only
        if (!preg_match('/^[\p{L}\p{N}]+$/u', $val)) {
            $errors[] = $fieldName . ' môže obsahovať len písmená a čísla.';
        }
        return $errors;
    }

    /**
     * Validate email and uniqueness. Returns array of error messages.
     * @param string|null $email
     * @param int|null $currentUserId when provided, allow that user to keep the same email
     * @return string[]
     */
    protected static function validateEmail(?string $email, ?int $currentUserId = null): array
    {
        $errors = [];
        $val = $email === null ? '' : trim((string)$email);
        if ($val === '') {
            $errors[] = 'Email je povinný.';
            return $errors;
        }
        if (strlen($val) > 30) {
            $errors[] = 'Email je príliš dlhý';
        }
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email má nesprávny formát.';
            return $errors;
        }
        $existing = static::findByEmail($val);
        if ($existing !== null && ($currentUserId === null || $existing->id !== $currentUserId)) {
            $errors[] = 'Používateľ s týmto emailom už existuje.';
        }
        return $errors;
    }

    /**
     * Validate password and confirm. If $required=true then password must be present.
     * @param string|null $password
     * @param string|null $confirm
     * @param bool $required
     * @return string[]
     */
    protected static function validatePassword(?string $password, ?string $confirm, bool $required = false): array
    {
        $errors = [];
        $pw = $password === null ? '' : (string)$password;
        $pwConf = $confirm === null ? '' : (string)$confirm;

        if ($required && $pw === '') {
            $errors[] = 'Heslo je povinné.';
            return $errors;
        }

        if ($pw !== '') {
            if ($pw !== $pwConf) {
                $errors[] = 'Heslá sa nezhodujú.';
            }
            if (mb_strlen($pw) < 6) {
                $errors[] = 'Heslo musí mať aspoň 6 znakov.';
            }
        }

        return $errors;
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

        // validate name(s)
        $errors = array_merge($errors, static::validateName($firstName, 'Meno'));
        $errors = array_merge($errors, static::validateName($lastName, 'Priezvisko'));

        // validate email (allow keeping current email)
        $errors = array_merge($errors, static::validateEmail($email, $this->id ?? null));

        // validate password only if provided
        $errors = array_merge($errors, static::validatePassword($password, $passwordConfirm, false));

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

    /**
     * Create a new user from provided data. Returns array with keys:
     * - 'user' => created User instance or null on failure
     * - 'errors' => array of validation error messages
     * Expected keys in $data: firstName, lastName, email, password, passwordConfirm, role
     * @param array $data
     * @return array{user:?static, errors:array}
     */
    public static function create(array $data): array
    {
        $errors = [];

        $firstName = isset($data['firstName']) ? trim((string)$data['firstName']) : '';
        $lastName = isset($data['lastName']) ? trim((string)$data['lastName']) : '';
        $email = isset($data['email']) ? trim((string)$data['email']) : '';
        $password = $data['password'] ?? null;
        $passwordConfirm = $data['passwordConfirm'] ?? null;
        $role = isset($data['role']) ? trim((string)$data['role']) : 'student';

        // validate names
        $errors = array_merge($errors, static::validateName($firstName, 'Meno'));
        $errors = array_merge($errors, static::validateName($lastName, 'Priezvisko'));

        // validate email (for create, no current user id)
        $errors = array_merge($errors, static::validateEmail($email, null));

        // password required on create
        $errors = array_merge($errors, static::validatePassword($password, $passwordConfirm, true));

        if (!empty($errors)) {
            return ['user' => null, 'errors' => $errors];
        }

        $user = new static();
        $user->firstName = $firstName;
        $user->lastName = $lastName;
        $user->email = $email;
        $user->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user->role = $role !== '' ? $role : 'student';

        try {
            $user->save();
            return ['user' => $user, 'errors' => []];
        } catch (\Throwable $e) {
            return ['user' => null, 'errors' => ['Chyba pri vytváraní používateľa: ' . $e->getMessage()]];
        }
    }

}
