<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Class Course
 *
 * Model for the `courses` table.
 * Columns (DB / property):
 * - id -> id
 * - teacher_id -> teacherId
 * - name -> name
 * - description -> description
 * - credits -> credits
 */
class Course extends Model
{
    // Optional explicit table name (conventions would resolve this automatically)
    protected static ?string $tableName = 'courses';
    protected static array $columnsMap = [
        'teacher_id' => 'teacherId',
    ];

    public ?int $id = null;
    public ?int $teacherId = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?int $credits = null;

    /**
     * Convenience: load the related Teacher record (simple DB lookup).
     * Returns null if teacher_id is not set or teacher not found.
     * @return Teacher|null
     */
    public function getTeacher(): ?Teacher
    {
        if ($this->teacherId === null) {
            return null;
        }
        return Teacher::getOne($this->teacherId);
    }

    /**
     * Find courses by teacher id
     * @param int $teacherId
     * @param int|null $limit
     * @return static[]
     */
    public static function findByTeacherId(int $teacherId, ?int $limit = null): array
    {
        return static::getAll('teacher_id = ?', [$teacherId], null, $limit);
    }

    /**
     * Find course by exact name
     * @param string $name
     * @return static|null
     */
    public static function findByName(string $name): ?static
    {
        $items = static::getAll('name = ?', [$name], null, 1);
        return $items[0] ?? null;
    }

    // ---------------- convenience wrappers ----------------

    /**
     * Wrapper for retrieving all courses.
     * @return static[]
     */
    public static function getAllCourses(): array
    {
        return static::getAll();
    }

    /**
     * Wrapper to get a single course by id (alias for Model::getOne)
     * @param int $id
     * @return static|null
     */
    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }
}
