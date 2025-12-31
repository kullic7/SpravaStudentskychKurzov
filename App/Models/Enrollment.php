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
    protected static array $columnsMap = [
        'student_id' => 'studentId',
        'course_id'  => 'courseId',
    ];

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

    // ---------------- convenience wrappers ----------------

    /**
     * Return enrollments with pending/not-approved statuses.
     * @param array|null $statuses optional array of status strings to treat as pending
     * @return static[]
     */
    public static function getPendingEnrollments(?array $statuses = null): array
    {
        $statuses = $statuses ?? ['not_approved', 'not approved', 'not aproved', 'pending'];
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
        return static::getAll("status IN ($placeholders)", $statuses);
    }

    /**
     * Return the number of enrollments with pending/not-approved statuses.
     * @param array|null $statuses optional array of status strings to treat as pending
     * @return int
     */
    public static function getPendingCount(?array $statuses = null): int
    {
        $statuses = $statuses ?? ['not_approved', 'not approved', 'not aproved', 'pending'];
        if (empty($statuses)) return 0;
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
        return static::getCount("status IN ($placeholders)", $statuses);
    }

    /**
     * Return the number of enrollments for a specific student that match a given status.
     * @param int $studentId
     * @param string|null $status Optional status to filter by (default 'approved'). If null, counts all enrollments for the student.
     * @return int
     */
    public static function countByStudent(int $studentId, ?string $status = 'approved'): int
    {
        if ($status === null) {
            return static::getCount('student_id = ?', [$studentId]);
        }
        return static::getCount('student_id = ? AND status = ?', [$studentId, $status]);
    }

    /**
     * Return the number of pending enrollments for a specific student.
     * @param int $studentId
     * @param array|null $statuses
     * @return int
     */
    public static function pendingCountByStudent(int $studentId, ?array $statuses = null): int
    {
        $statuses = $statuses ?? ['not_approved', 'not approved', 'not aproved', 'pending'];
        if (empty($statuses)) return 0;
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
        $where = "student_id = ? AND status IN ($placeholders)";
        $params = array_merge([$studentId], $statuses);
        return static::getCount($where, $params);
    }

    /**
     * Create a new enrollment for a student and course.
     * Returns ['enrollment' => Enrollment|null, 'errors' => array].
     * Will not create a duplicate enrollment if one already exists for the same student+course.
     * @param int $studentId
     * @param int $courseId
     * @param array $data optional, supports 'status' and 'grade'
     * @return array{enrollment:?static, errors:array}
     */
    public static function create(int $studentId, int $courseId, array $data = []): array
    {
        $errors = [];

        // basic validation
        if ($studentId <= 0) {
            $errors[] = 'Neplatné ID študenta.';
        }
        if ($courseId <= 0) {
            $errors[] = 'Neplatné ID kurzu.';
        }

        if (!empty($errors)) {
            return ['enrollment' => null, 'errors' => $errors];
        }

        // check duplicate
        $exists = static::getCount('student_id = ? AND course_id = ?', [$studentId, $courseId]);
        if ($exists > 0) {
            return ['enrollment' => null, 'errors' => ['Študent je už zapísaný na tento kurz.']];
        }

        $en = new static();
        $en->studentId = $studentId;
        $en->courseId = $courseId;
        $en->status = $data['status'] ?? 'not_approved';
        $en->grade = $data['grade'] ?? null;

        try {
            $en->save();
            return ['enrollment' => $en, 'errors' => []];
        } catch (\Throwable $e) {
            return ['enrollment' => null, 'errors' => ['Chyba pri ukladaní zápisu: ' . $e->getMessage()]];
        }
    }

    /**
     * Compute average numeric grade for a student across enrollments that have a grade.
     * Supports numeric grades and letter grades mapped as: A=1, B=1.5, C=2, D=3, E=4, Fx=5.
     * Returns float rounded to 2 decimals or null if no valid grades available.
     * @param int $studentId
     * @return float|null
     */
    public static function averageGradeByStudent(int $studentId): ?float
    {
        // fetch enrollments for student that have a non-empty grade
        $items = static::getAll('student_id = ? AND grade IS NOT NULL AND grade <> ?', [$studentId, '']);
        $sum = 0.0;
        $count = 0;

        // mapping for letter grades (case-insensitive)
        $letterMap = [
            'A' => 1.0,
            'B' => 1.5,
            'C' => 2.0,
            'D' => 3.0,
            'E' => 4.0,
            'FX' => 5.0,
            'F' => 5.0, // be lenient: treat F as Fx
        ];

        foreach ($items as $it) {
            $raw = $it->grade ?? '';
            $g = trim((string)$raw);
            if ($g === '') continue;

            // try letter grade first (exact tokens like A, B, C, D, E, FX, F)
            $upper = mb_strtoupper($g);
            // extract first token of letters (e.g. 'B+' -> 'B', 'fx' -> 'FX')
            if (preg_match('/^([A-Za-z]{1,2})\b/', $upper, $m)) {
                $tok = $m[1];
                // normalize 'F' as 'FX'
                if ($tok === 'F') $tok = 'FX';
                if (array_key_exists($tok, $letterMap)) {
                    $sum += $letterMap[$tok];
                    $count++;
                    continue;
                }
            }

            // fallback: try to find numeric token inside string (supports comma or dot)
            if (preg_match('/(-?\d+[.,]?\d*)/', $g, $m)) {
                $numStr = str_replace(',', '.', $m[1]);
                if (is_numeric($numStr)) {
                    $sum += (float)$numStr;
                    $count++;
                    continue;
                }
            }

            // otherwise skip (non-numeric, non-mapped grade)
        }

        if ($count === 0) {
            return null;
        }

        $avg = $sum / $count;
        return (float) round($avg, 2);
    }

    /**
     * Compute average numeric grade for a course across enrollments that have a grade and are approved.
     * Uses the same letter->numeric mapping as averageGradeByStudent.
     * Returns float (not rounded) or null if no valid grades available.
     * @param int $courseId
     * @return float|null
     */
    public static function averageGradeByCourse(int $courseId): ?float
    {
        // fetch enrollments for course that are approved and have a non-empty grade
        $items = static::getAll('course_id = ? AND status = ? AND grade IS NOT NULL AND grade <> ?', [$courseId, 'approved', '']);
        $sum = 0.0;
        $count = 0;

        $letterMap = [
            'A'  => 1.0,
            'B'  => 1.5,
            'C'  => 2.0,
            'D'  => 3.0,
            'E'  => 4.0,
            'FX' => 5.0,
            'F'  => 5.0,
        ];

        foreach ($items as $it) {
            $raw = $it->grade ?? '';
            $g = trim((string)$raw);
            if ($g === '') continue;

            $upper = mb_strtoupper($g);
            if (preg_match('/^([A-Za-z]{1,2})\b/', $upper, $m)) {
                $tok = $m[1];
                if ($tok === 'F') $tok = 'FX';
                if (array_key_exists($tok, $letterMap)) {
                    $sum += $letterMap[$tok];
                    $count++;
                    continue;
                }
            }

            if (preg_match('/(-?\d+[.,]?\d*)/', $g, $m)) {
                $numStr = str_replace(',', '.', $m[1]);
                if (is_numeric($numStr)) {
                    $sum += (float)$numStr;
                    $count++;
                    continue;
                }
            }
        }

        if ($count === 0) return null;
        return (float) ($sum / $count);
    }

    /**
     * Approve an enrollment by id (set status = 'approved'). Returns true if updated, false otherwise.
     * @param int $id
     * @return bool
     */
    public static function approveById(int $id): bool
    {
        $en = static::getOne($id);
        if ($en === null) return false;
        $en->status = 'approved';
        $en->save();
        return true;
    }

    /**
     * Validate enrollment grade update input.
     *
     * @param int $id
     * @param string|null $grade
     * @return array{enrollment:?static, grade:?string, errors:string[], message:?string}
     */
    protected static function validateGrade(int $id, ?string $grade): array
    {
        if ($id <= 0) {
            return [
                'enrollment' => null,
                'grade' => null,
                'message' => 'Neplatné ID zápisu.',
                'errors' => ['Neplatné ID zápisu.'],
            ];
        }

        $en = static::getOne($id);
        if ($en === null) {
            return [
                'enrollment' => null,
                'grade' => null,
                'message' => 'Zápis nebol nájdený.',
                'errors' => ['Zápis nebol nájdený.'],
            ];
        }

        $gradeVal = $grade === null ? null : trim((string)$grade);
        if ($gradeVal === '') {
            $gradeVal = null;
        }

        if ($gradeVal !== null) {
            $upper = mb_strtoupper($gradeVal);
            if (!preg_match('/^(?:[A-E]|FX)$/', $upper)) {
                return [
                    'enrollment' => null,
                    'grade' => null,
                    'message' => 'Známka musí byť A, B, C, D, E alebo Fx.',
                    'errors' => ['Neplatný formát známky.'],
                ];
            }
            $gradeVal = $upper; // normalizácia
        }

        return [
            'enrollment' => $en,
            'grade' => $gradeVal,
            'message' => null,
            'errors' => [],
        ];
    }


    public static function updateZnamky(int $id, ?string $grade): array
    {
        $result = static::validateGrade($id, $grade);

        if (!empty($result['errors'])) {
            return [
                'success' => false,
                'grade' => null,
                'message' => $result['message'],
                'errors' => $result['errors'],
            ];
        }

        $en = $result['enrollment'];
        $en->grade = $result['grade'];

        try {
            $en->save();
            return [
                'success' => true,
                'grade' => $result['grade'],
                'errors' => [],
            ];
        } catch (\Throwable $e) {
            $msg = 'Chyba pri ukladaní: ' . $e->getMessage();
            return [
                'success' => false,
                'grade' => null,
                'message' => $msg,
                'errors' => [$msg],
            ];
        }
    }


}
