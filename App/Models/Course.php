<?php

namespace App\Models;
use Framework\Core\Model;
class Course extends Model
{
    protected static ?string $tableName = 'courses';
    protected static array $columnsMap = [
        'teacher_id' => 'teacherId',
    ];
    public ?int $id = null;
    public ?int $teacherId = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?int $credits = null;

    public function getTeacher(): ?Teacher
    {
        if ($this->teacherId === null) {
            return null;
        }
        return Teacher::getOne($this->teacherId);
    }

    public static function findByTeacherId(int $teacherId, ?int $limit = null): array
    {
        return static::getAll('teacher_id = ?', [$teacherId], null, $limit);
    }

    public static function getAllCourses(): array
    {
        return static::getAll();
    }

    public static function findById(int $id): ?static
    {
        return static::getOne($id);
    }
    //chatgpt
    protected static function validateData(array $data, ?int $currentId = null): array
    {
        $errors = [];
        $values = [];

        // ---- name ----
        if (array_key_exists('name', $data)) {
            $name = trim((string)$data['name']);

            if ($name === '') {
                $errors[] = 'Názov kurzu je povinný.';
            }
            // max length
            elseif (mb_strlen($name) > 50) {
                $errors[] = 'Názov kurzu môže mať maximálne 50 znakov.';
            }
            // only letters and numbers (unicode safe)
            elseif (!preg_match('/^[\p{L}\p{N} ]+$/u', $name)) {
                $errors[] = 'Názov kurzu môže obsahovať len písmená, čísla.';
            }
            else {
                $params = [$name];
                $sql = 'LOWER(name) = LOWER(?)';

                if ($currentId !== null) {
                    $sql .= ' AND id != ?';
                    $params[] = $currentId;
                }

                if (!empty(static::getAll($sql, $params, null, 1))) {
                    $errors[] = 'Kurz s týmto názvom už existuje.';
                } else {
                    $values['name'] = $name;
                }
            }
        }

        // ---- description ----
        if (array_key_exists('description', $data)) {
            $desc = trim((string)$data['description']);
            $values['description'] = $desc === '' ? null : $desc;
        }

        // ---- credits ----
        if (array_key_exists('credits', $data)) {
            if ($data['credits'] === '' || $data['credits'] === null) {
                $values['credits'] = null;
            } else {
                $credits = (int)$data['credits'];
                if ($credits < 0) {
                    $errors[] = 'Kredity musia byť nezáporné číslo.';
                } else {
                    $values['credits'] = $credits;
                }
            }
        }

        // ---- teacher ----
        if (array_key_exists('teacherId', $data)) {
            if ($data['teacherId'] === '' || $data['teacherId'] === null) {
                $values['teacherId'] = null;
            } else {
                $tid = (int)$data['teacherId'];
                if (Teacher::findById($tid) === null) {
                    $errors[] = 'Vybraný učiteľ neexistuje.';
                } else {
                    $values['teacherId'] = $tid;
                }
            }
        }

        return ['values' => $values, 'errors' => $errors];
    }

    public static function create(array $data): array
    {
        $result = static::validateData($data, null);

        if (!empty($result['errors'])) {
            return ['course' => null, 'errors' => $result['errors']];
        }

        $course = new static();

        foreach ($result['values'] as $key => $value) {
            $course->$key = $value;
        }

        try {
            $course->save();
            return ['course' => $course, 'errors' => []];
        } catch (\Exception $e) {

            return [
                'course' => null,
                'errors' => ['Kurz sa nepodarilo vytvoriť.'],
            ];
        }
    }

    public function update(array $data): array
    {
        $result = static::validateData($data, $this->id);

        if (!empty($result['errors'])) {
            return $result['errors'];
        }

        foreach ($result['values'] as $key => $value) {
            $this->$key = $value;
        }

        $this->save();
        return [];
    }
}
