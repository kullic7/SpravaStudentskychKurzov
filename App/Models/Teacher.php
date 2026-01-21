<?php

namespace App\Models;

use Framework\Core\Model;
class Teacher extends Model
{
    protected static ?string $tableName = 'teachers';
    protected static array $columnsMap = [
        'user_id' => 'userId',
    ];
    public ?int $id = null;
    public ?int $userId = null;
    public ?string $department = null;

    public function getUser(): ?User
    {
        if ($this->userId === null) {
            return null;
        }
        return User::getOne($this->userId);
    }

    public static function findByUserId(int $userId): ?static
    {
        $items = static::getAll('user_id = ?', [$userId], null, 1);
        return $items[0] ?? null;
    }

    public static function getAllTeachers(): array
    {
        return static::getAll();
    }

    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }

    public function update(array $data): array
    {
        $errors = [];

        $this->validateDepartment($data['department'] ?? null, $errors);

        if (!empty($errors)) {
            return $errors;
        }

        $this->save();
        return [];
    }


    private function validateDepartment(?string $input, array &$errors): void
    {
        if ($input === null) {
            return;
        }

        $dep = trim($input);
        if ($dep === '') {
            $this->department = null;
            return;
        }

        if (mb_strlen($dep) >= 255) {
            $errors[] = 'Oddelenie musí byť kratšie ako 255 znakov.';
            return;
        }

        if (!preg_match('/^[\p{L}\- ]+$/u', $dep)) {
            $errors[] = 'Oddelenie môže obsahovať len písmená, medzeru a pomlčku.';
            return;
        }

        $this->department = $dep;
    }

    public static function create(int $userId, array $data): array
    {
        $teacher = new static();
        $teacher->userId = $userId;

        $errors = $teacher->update($data);
        if (!empty($errors)) {
            return ['teacher' => null, 'errors' => $errors];
        }

        return ['teacher' => $teacher, 'errors' => []];
    }
}