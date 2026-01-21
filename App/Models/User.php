<?php

namespace App\Models;

use Framework\Core\Model;
class User extends Model
{
    protected static ?string $tableName = 'users';
    protected static array $columnsMap = [
        'first_name' => 'firstName',
        'last_name'  => 'lastName',
        'password_hash' => 'passwordHash',
        'created_at' => 'createdAt',
    ];

    public ?int $id = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
    public ?string $passwordHash = null;
    public ?string $role = null;
    public ?string $createdAt = null;

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

    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }

    public static function getAllUsers(): array
    {
        return static::getAll();
    }

    public static function findByEmail(string $email): ?static
    {
        $items = static::getAll('email = ?', [$email], null, 1);
        return $items[0] ?? null;
    }

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

        if (!preg_match('/^[\p{L}]+$/u', $val)) {
            $errors[] = $fieldName . ' môže obsahovať len písmená.';
        }
        return $errors;
    }

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

    protected static function validatePassword(?string $passwordOld, ?string $password, ?string $confirm, bool $required = false): array
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

    public function updateProfile(array $data, bool $isAdmin): array
    {
        $result = static::validateProfileData($data, $this->id, false, $this->passwordHash, false, $isAdmin);

        if (!empty($result['errors'])) {
            return $result['errors'];
        }

        $this->applyProfileData($result);
        $this->save();

        return [];
    }

    public static function create(array $data): array
    {
        $result = static::validateProfileData($data, null, true, null, true);

        if (!empty($result['errors'])) {
            return ['user' => null, 'errors' => $result['errors']];
        }

        $user = new static();
        $user->applyProfileData($result);
        $user->role = trim((string)($data['role'] ?? 'student')) ?: 'student';

        try {
            $user->save();
            return ['user' => $user, 'errors' => []];
        } catch (\Throwable $e) {
            return [
                'user' => null,
                'errors' => ['Chyba pri vytváraní používateľa: ' . $e->getMessage()],
            ];
        }
    }

    private function applyProfileData(array $data): void
    {
        $this->firstName = $data['firstName'];
        $this->lastName  = $data['lastName'];
        $this->email     = $data['email'];

        if (!empty($data['password'])) {
            $this->passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        }
    }
    //chat gpt
    private static function validateProfileData(
        array $data,
        ?int $currentUserId,
        bool $passwordRequired,
        ?string $currentPasswordHash = null,
        bool $isCreation = false,
        bool $isAdmin = false
    ): array {
        $errors = [];

        $firstName = isset($data['firstName']) ? trim((string)$data['firstName']) : '';
        $lastName  = isset($data['lastName'])  ? trim((string)$data['lastName'])  : '';
        $email     = isset($data['email'])     ? trim((string)$data['email'])     : '';
        $oldPassword = $data['passwordOld'] ?? null;
        $password  = $data['password'] ?? null;
        $confirm   = $data['passwordConfirm'] ?? null;


        $errors = array_merge($errors, static::validateName($firstName, 'Meno'));
        $errors = array_merge($errors, static::validateName($lastName, 'Priezvisko'));
        $errors = array_merge($errors, static::validateEmail($email, $currentUserId));
        $errors = array_merge($errors, static::validatePassword($oldPassword, $password, $confirm, $passwordRequired));

        $pw = $password === null ? '' : (string)$password;
        if (!$isCreation && !$isAdmin) {
            if ($pw !== '') {
                if ($currentPasswordHash === null) {
                    $errors[] = 'Nie je možné overiť staré heslo.';
                } else {
                    if (empty($oldPassword) || !password_verify((string)$oldPassword, $currentPasswordHash)) {
                        $errors[] = 'Staré heslo je nesprávne.';
                    }
                }
            }
        }


        return [
            'errors'    => $errors,
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
            'password'  => $password,
        ];
    }
}