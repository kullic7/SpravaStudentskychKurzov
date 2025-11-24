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
}

