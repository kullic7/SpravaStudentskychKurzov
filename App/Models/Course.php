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

    /**
     * Validate and normalize course input data.
     *
     * @param array $data
     * @param int|null $currentId  ID kurzu pri update (null pri create)
     * @return array{values: array, errors: string[]}
     */
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
        } catch (\Throwable $e) {
            return ['course' => null, 'errors' => ['Chyba pri vytváraní kurzu: ' . $e->getMessage()]];
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
