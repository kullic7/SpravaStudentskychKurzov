<?php

namespace App\Controllers;

use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class TeacherController extends BaseController
{
    public function index(Request $request): Response
    {
        return $this->html();
    }

    public function studenti(Request $request): Response
    {
        $user = $this->app->getAuthenticator()->getUser();
        if (!$user || $user->getRole() !== 'teacher') {
            return $this->redirect($this->url('auth.index'));
        }

        $teacher = Teacher::findByUserId((int)$user->getId());
        if (!$teacher) {
            return $this->html([
                'students' => [],
                'teacher' => null,
                'courses' => [],
            ]);
        }

        $courses = Course::findByTeacherId((int)$teacher->id);
        if (empty($courses)) {
            return $this->html([
                'students' => [],
                'teacher' => $teacher,
                'courses' => [],
            ]);
        }

        $students = $this->loadStudentsForTeacherCourses($courses);

        return $this->html([
            'students' => $students,
            'teacher' => $teacher,
            'courses' => $courses,
        ]);
    }


    //chat gpt
    private function loadStudentsForTeacherCourses(array $courses): array
    {
        $coursesById = [];
        $courseIds = [];

        foreach ($courses as $c) {
            $coursesById[$c->id] = $c;
            $courseIds[] = $c->id;
        }

        $placeholders = implode(', ', array_fill(0, count($courseIds), '?'));

        $enrollments = Enrollment::getAll(
            "course_id IN ($placeholders) AND status = ?",
            [...$courseIds, 'approved']
        );

        $studentsMap = [];

        foreach ($enrollments as $en) {
            if (!$en->studentId || !$en->courseId) {
                continue;
            }

            if (!isset($studentsMap[$en->studentId])) {
                $student = Student::findById((int)$en->studentId);
                if (!$student) continue;

                $studentsMap[$en->studentId] = [
                    'student' => $student,
                    'user' => $student->getUser(),
                    'courses' => [],
                ];
            }

            $studentsMap[$en->studentId]['courses'][$en->courseId] = [
                'course' => $coursesById[$en->courseId],
                'enrollment' => $en,
            ];
        }

        return array_values($studentsMap);
    }

    public function updateGrade(Request $request): Response
    {
        $user = $this->app->getAuthenticator()->getUser();
        if (!$user || $user->getRole() !== 'teacher') {
            return $this->deny($request, 'Nemáte oprávnenie.');
        }

        $teacher = Teacher::findByUserId((int)$user->getId());
        if (!$teacher) {
            return $this->deny($request, 'Nenašiel sa učiteľ.');
        }

        $enrollmentId = $request->post('enrollmentId');
        $grade = $request->post('grade');

        if (!$enrollmentId) {
            return $this->deny($request, 'Chýba ID zápisu.');
        }

        $result = Enrollment::updateZnamky((int)$enrollmentId, $grade);

        if ($request->isAjax()) {
            return $this->json([
                'success' => !empty($result['success']),
                'grade'   => $result['grade'] ?? null,
                'message' => $result['message'] ?? null,
            ]);
        }

        return $this->redirect($this->url('teacher.studenti'));
    }


    private function deny(Request $request, string $message): Response
    {
        if ($request->isAjax()) {
            return $this->json([
                'success' => false,
                'message' => $message,
            ]);
        }

        return $this->redirect($this->url('auth.index'));
    }
}