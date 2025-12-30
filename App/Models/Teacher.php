<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Class Teacher
 *
 * Model for the `teachers` table.
 * Columns (DB / property):
 * - id -> id
 * - user_id -> userId
 * - department -> department
 */
class Teacher extends Model
{
    // Optional explicit table name (conventions would resolve this automatically)
    protected static ?string $tableName = 'teachers';
    protected static array $columnsMap = [
        'user_id' => 'userId',
    ];

    public ?int $id = null;
    public ?int $userId = null;
    public ?string $department = null;

    /**
     * Convenience: load the related User record (simple DB lookup).
     * Returns null if user_id is not set or user not found.
     * @return User|null
     */
    public function getUser(): ?User
    {
        if ($this->userId === null) {
            return null;
        }
        return User::getOne($this->userId);
    }

    /**
     * Find teacher by user id
     * @param int $userId
     * @return static|null
     */
    public static function findByUserId(int $userId): ?static
    {
        $items = static::getAll('user_id = ?', [$userId], null, 1);
        return $items[0] ?? null;
    }

    /**
     * Find teachers by department
     * @param string $department
     * @param int|null $limit
     * @return static[]
     */
    public static function findByDepartment(string $department, ?int $limit = null): array
    {
        return static::getAll('department = ?', [$department], null, $limit);
    }

    // ---------------- convenience wrappers ----------------

    /**
     * Wrapper for retrieving all teachers.
     * @return static[]
     */
    public static function getAllTeachers(): array
    {
        return static::getAll();
    }

    /**
     * Wrapper to get a single teacher by id (alias for Model::getOne)
     * @param int $id
     * @return static|null
     */
    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }

    /**
     * Update teacher fields from provided data and save.
     * Expected keys: 'department'.
     * Returns array of errors (empty on success).
     * @param array $data
     * @return array<string>
     */
    public function update(array $data): array
    {
        $errors = [];

        $department = isset($data['department']) ? trim((string)$data['department']) : null;

        if ($department !== null) {
            $dep = $department === '' ? null : $department;
            if ($dep !== null) {
                // length must be less than 255 characters
                if (mb_strlen($dep) >= 255) {
                    $errors[] = 'Oddelenie musí byť kratšie ako 255 znakov.';
                }
                // allow only letters (Unicode) and hyphen
                if (!preg_match('/^[\p{L}-]+$/u', $dep)) {
                    $errors[] = 'Oddelenie môže obsahovať len písmená a pomlčku.';
                }
                // set value only if no related errors
                if (empty($errors)) {
                    $this->department = $dep;
                }
            } else {
                $this->department = null;
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $this->save();
        return [];
    }

    /**
     * Create a teacher associated with a user id.
     * Returns ['teacher' => Teacher|null, 'errors' => array].
     * Expected keys: department
     * @param int $userId
     * @param array $data
     * @return array{teacher:?static, errors:array}
     */
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
