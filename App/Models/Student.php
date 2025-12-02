<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Class Student
 *
 * Model for the `students` table.
 * Columns (DB / property):
 * - id -> id
 * - user_id -> userId
 * - student_number -> studentNumber
 * - year -> year
 */
class Student extends Model
{
    // Optional explicit table name (conventions would resolve this automatically)
    protected static ?string $tableName = 'students';
    protected static array $columnsMap = [
        'user_id' => 'userId',
        'student_number'  => 'studentNumber',
    ];

    public ?int $id = null;
    public ?int $userId = null;
    public ?string $studentNumber = null;
    public ?int $year = null;

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
     * Find student by student number
     * @param string $studentNumber
     * @return static|null
     */
    public static function findByStudentNumber(string $studentNumber): ?static
    {
        $items = static::getAll('student_number = ?', [$studentNumber], null, 1);
        return $items[0] ?? null;
    }

    /**
    // ---------------- convenience wrappers ----------------

    /**
     * Wrapper for retrieving all students.
     * @return static[]
     */
    public static function getAllStudents(): array
    {
        return static::getAll();
    }

    /**
     * Wrapper to get a single student by id (alias for Model::getOne)
     * @param int $id
     * @return static|null
     */
    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }


}
