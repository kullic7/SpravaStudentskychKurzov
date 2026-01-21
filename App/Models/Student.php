<?php

namespace App\Models;

use Framework\Core\Model;
class Student extends Model
{
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

    public function getUser(): ?User
    {
        if ($this->userId === null) {
            return null;
        }
        return User::getOne($this->userId);
    }

    public static function getAllStudents(): array
    {
        return static::getAll();
    }

    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }

    public static function findByUserId(int $userId): ?static
    {
        $items = static::getAll('user_id = ?', [$userId], null, 1);
        return $items[0] ?? null;
    }

    public function update(array $data): array
    {
        $errors = [];

        $this->validateStudentNumber($data['studentNumber'] ?? null, $errors);
        $this->validateYear($data['year'] ?? null, $errors);

        if (!empty($errors)) {
            return $errors;
        }

        $this->save();
        return [];
    }

    private function validateStudentNumber(?string $input, array &$errors): void
    {
        if ($input === null) {
            return;
        }

        $sn = trim((string)$input);
        if ($sn === '') {
            $this->studentNumber = null;
            return;
        }

        $sn = strtoupper($sn);

        if (!preg_match('/^S\d{4}$/', $sn)) {
            $errors[] = "Študijné číslo musí mať formát 'S1234' (S + 4 číslice).";
            return;
        }

        $exists = $this->id !== null
            ? static::getCount('student_number = ? AND id != ?', [$sn, $this->id])
            : static::getCount('student_number = ?', [$sn]);

        if ($exists > 0) {
            $errors[] = 'Toto študijné číslo už používa iný študent.';
            return;
        }

        $this->studentNumber = $sn;
    }
    private function validateYear($year, array &$errors): void
    {
        if ($year === null || $year === '') {
            $this->year = null;
            return;
        }

        if (!preg_match('/^\d+$/', (string)$year)) {
            $errors[] = 'Ročník musí byť číslo.';
            return;
        }

        $yearInt = (int)$year;
        if ($yearInt < 1) {
            $errors[] = 'Ročník musí byť kladné číslo.';
            return;
        }

        $this->year = $yearInt;
    }

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
