<?php

namespace App\Models;

use Framework\Core\Model;
class Student extends Model
{
    // Optional explicit table name (conventions would resolve this automatically)
    protected static ?string $tableName = 'students';

    protected static array $columnsMap = [
        'id' => 'id',
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

    /**
     * Find a student record by the associated user id.
     * @param int $userId
     * @return static|null
     */
    public static function findByUserId(int $userId): ?static
    {
        $items = static::getAll('user_id = ?', [$userId], null, 1);
        return $items[0] ?? null;
    }

    /**
     * Update student fields from provided data and save.
     * Expected keys: 'studentNumber', 'year'.
     * Returns array of errors (empty on success).
     * @param array $data
     * @return array<string>
     */
    public function update(array $data): array
    {
        $errors = [];

        $studentNumber = isset($data['studentNumber']) ? trim((string)$data['studentNumber']) : null;
        $year = $data['year'] ?? null;

        // Validate and set student number if provided (empty string => null)
        if ($studentNumber !== null) {
            $sn = $studentNumber === '' ? null : strtoupper($studentNumber);

            if ($sn !== null) {
                // must match format S1234 (S + 4 digits)
                if (!preg_match('/^S\d{4}$/', $sn)) {
                    $errors[] = "Študijné číslo musí mať formát 'S1234' (S následované 4 číslicami).";
                } else {
                    // uniqueness check: exclude current record when updating
                    if ($this->id !== null) {
                        $exists = static::getCount('student_number = ? AND id != ?', [$sn, $this->id]);
                    } else {
                        $exists = static::getCount('student_number = ?', [$sn]);
                    }
                    if ($exists > 0) {
                        $errors[] = 'Toto študijné číslo už používa iný študent.';
                    } else {
                        $this->studentNumber = $sn;
                    }
                }
            } else {
                $this->studentNumber = null;
            }
        }

        // Year handling
        if ($year !== null && $year !== '') {
            // ensure year is numeric (digits only)
            $yearStr = (string)$year;
            if (!preg_match('/^\d+$/', $yearStr)) {
                $errors[] = 'Ročník musí byť číslo.';
            } else {
                $yearInt = (int)$yearStr;
                if ($yearInt < 1) {
                    $errors[] = 'Ročník musí byť kladné číslo.';
                } else {
                    $this->year = $yearInt;
                }
            }
        } else {
            $this->year = null;
        }

        if (!empty($errors)) {
            return $errors;
        }

        $this->save();
        return [];
    }

    /**
     * Create a student associated with a user id.
     * Returns ['student' => Student|null, 'errors' => array].
     * Expected keys: studentNumber, year
     * @param int $userId
     * @param array $data
     * @return array{student:?static, errors:array}
     */
    public static function create(int $userId, array $data): array
    {
        $student = new static();
        $student->userId = $userId;

        $errors = $student->update($data);
        if (!empty($errors)) {
            return ['student' => null, 'errors' => $errors];
        }

        return ['student' => $student, 'errors' => []];
    }


}
