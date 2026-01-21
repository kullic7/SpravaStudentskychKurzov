<?php

namespace App\Models;
use Framework\Core\Model;

class Enrollment extends Model
{
    public const STATUS_NOT_APPROVED = 'not_approved';
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

    public function getStudent(): ?Student
    {
        if ($this->studentId === null) {
            return null;
        }
        return Student::getOne($this->studentId);
    }

    public function getCourse(): ?Course
    {
        if ($this->courseId === null) {
            return null;
        }
        return Course::getOne($this->courseId);
    }

    public static function getPendingEnrollments(): array
    {
        return static::getAll(
            'status = ?',
            [self::STATUS_NOT_APPROVED]
        );
    }

    public static function getPendingCount(?array $statuses = null): int
    {
        $statuses = $statuses ?? [self::STATUS_NOT_APPROVED];

        if (empty($statuses)) {
            return 0;
        }
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
        return static::getCount("status IN ($placeholders)", $statuses);
    }

    public static function countByStudent(int $studentId, ?string $status = 'approved'): int
    {
        if ($status === null) {
            return static::getCount('student_id = ?', [$studentId]);
        }
        return static::getCount('student_id = ? AND status = ?', [$studentId, $status]);
    }

    public static function pendingCountByStudent(int $studentId): int
    {
        return static::getCount(
            'student_id = ? AND status = ?',
            [$studentId, self::STATUS_NOT_APPROVED]
        );
    }

    public static function getPendingByStudentId(int $studentId): array
    {
        return self::getAll(
            'student_id = ? AND status = ?',
            [$studentId, self::STATUS_NOT_APPROVED]
        );
    }

    public static function create(int $studentId, int $courseId, array $data = []): array
    {
        if ($studentId <= 0 || $courseId <= 0) {
            return [
                'enrollment' => null,
                'errors' => ['Neplatné ID študenta alebo kurzu.'],
            ];
        }

        // prevent duplicate enrollment
        if (static::getCount('student_id = ? AND course_id = ?', [$studentId, $courseId]) > 0) {
            return [
                'enrollment' => null,
                'errors' => ['Študent je už zapísaný na tento kurz.'],
            ];
        }

        $enrollment = new static();
        $enrollment->studentId = $studentId;
        $enrollment->courseId = $courseId;
        $enrollment->status = self::STATUS_NOT_APPROVED;
        $enrollment->grade = $data['grade'] ?? null;

        try {
            $enrollment->save();

            return [
                'enrollment' => $enrollment,
                'errors' => [],
            ];
        } catch (\Exception $e) {

            return [
                'enrollment' => null,
                'errors' => ['Zápis sa nepodarilo uložiť. Skúste neskôr.'],
            ];
        }
    }
    //chatgpt
    public static function averageGradeByStudent(int $studentId): ?float
    {
        $items = static::getAll(
            'student_id = ? AND grade IS NOT NULL AND grade <> ?',
            [$studentId, '']
        );

        $sum = 0.0;
        $count = 0;

        foreach ($items as $it) {
            $val = self::parseGrade((string)($it->grade ?? ''));
            if ($val === null) {
                continue;
            }

            $sum += $val;
            $count++;
        }

        return $count > 0 ? round($sum / $count, 2) : null;
    }
    //chatgpt
    private static function parseGrade(string $g): ?float
    {
        static $letterMap = [
            'A'  => 1.0,
            'B'  => 1.5,
            'C'  => 2.0,
            'D'  => 3.0,
            'E'  => 4.0,
            'FX' => 5.0,
            'F'  => 5.0,
        ];

        $g = trim($g);
        if ($g === '') {
            return null;
        }

        $upper = mb_strtoupper($g);

        // letter grade
        if (preg_match('/^([A-Z]{1,2})\b/', $upper, $m)) {
            $tok = $m[1] === 'F' ? 'FX' : $m[1];
            return $letterMap[$tok] ?? null;
        }

        // numeric grade
        if (preg_match('/(-?\d+[.,]?\d*)/', $g, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }

        return null;
    }
    //chatgpt
    public static function averageGradeByCourse(int $courseId): ?float
    {
        $items = static::getAll(
            'course_id = ? AND status = ? AND grade IS NOT NULL AND grade <> ?',
            [$courseId, 'approved', '']
        );

        $sum = 0.0;
        $count = 0;

        foreach ($items as $it) {
            $val = self::parseGrade((string)($it->grade ?? ''));
            if ($val === null) {
                continue;
            }

            $sum += $val;
            $count++;
        }

        return $count > 0 ? round($sum / $count, 2) : null;
    }

    public static function approveById(int $id): bool
    {
        $en = static::getOne($id);
        if ($en === null) return false;
        $en->status = 'approved';
        $en->save();
        return true;
    }

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
