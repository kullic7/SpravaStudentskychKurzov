<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Class Enrollment
 *
 * Model for the `enrollments` table.
 * Columns (DB / property):
 * - id -> id
 * - student_id -> studentId
 * - course_id -> courseId
 * - grade -> grade
 * - status -> status
 */
class Enrollment extends Model
{
    // Optional explicit table name
    protected static ?string $tableName = 'enrollments';

    public ?int $id = null;
    public ?int $studentId = null;
    public ?int $courseId = null;
    public ?string $grade = null;
    public ?string $status = null;

    /**
     * Load related Student record
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        if ($this->studentId === null) {
            return null;
        }
        return Student::getOne($this->studentId);
    }

    /**
     * Load related Course record
     * @return Course|null
     */
    public function getCourse(): ?Course
    {
        if ($this->courseId === null) {
            return null;
        }
        return Course::getOne($this->courseId);
    }

    /**
     * Find enrollments by student id
     * @param int $studentId
     * @param int|null $limit
     * @return static[]
     */
    public static function findByStudentId(int $studentId, ?int $limit = null): array
    {
        return static::getAll('student_id = ?', [$studentId], null, $limit);
    }

    /**
     * Find enrollments by course id
     * @param int $courseId
     * @param int|null $limit
     * @return static[]
     */
    public static function findByCourseId(int $courseId, ?int $limit = null): array
    {
        return static::getAll('course_id = ?', [$courseId], null, $limit);
    }

    /**
     * Find active enrollments for a student (status = 'active')
     * @param int $studentId
     * @return static[]
     */
    public static function findActiveByStudent(int $studentId): array
    {
        return static::getAll('student_id = ? AND status = ?', [$studentId, 'active']);
    }
}

