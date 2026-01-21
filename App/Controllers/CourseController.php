<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Enrollment;

class CourseController extends BaseController
{
    public function index(Request $request): Response
    {
        return $this->html();
    }
    //chat gpt
    public function kurzy(Request $request): Response
    {
        $session = $this->app->getSession();
        $errors = $session->get('errors');
        $session->remove('errors');

        $courses = Course::getAllCourses();
        $allTeachers = Teacher::getAllTeachers();

        $appUser = $this->app->getAuthenticator()->getUser();
        $role = $appUser ? $appUser->getRole() : null;

        $isAdmin = ($role === 'admin');
        $isStudent = ($role === 'student');

        $courseTeachers = $this->prepareCourseTeachers($courses);

        $studentEnrollmentsMap = $isStudent && $appUser
            ? $this->prepareStudentEnrollmentsMap((int)$appUser->getId())
            : [];

        return $this->html([
            'courses' => $courses,
            'courseTeachers' => $courseTeachers,
            'allTeachers' => $allTeachers,
            'isAdmin' => $isAdmin,
            'isStudent' => $isStudent,
            'studentEnrollmentsMap' => $studentEnrollmentsMap,
            'errors' => $errors,
        ], 'kurzy');
    }

    private function prepareCourseTeachers(array $courses): array
    {
        $result = [];

        foreach ($courses as $course) {
            $result[$course->id] = null;

            if (!$course->teacherId) {
                continue;
            }

            $teacher = Teacher::findById($course->teacherId);
            if (!$teacher) {
                continue;
            }

            $user = $teacher->getUser();
            if (!$user) {
                continue;
            }

            $result[$course->id] = (object)[
                'teacher' => $teacher,
                'user' => $user,
                'name' => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
            ];
        }

        return $result;
    }

    private function prepareStudentEnrollmentsMap(int $userId): array
    {
        $map = [];

        $student = Student::findByUserId($userId);
        if (!$student || !$student->id) {
            return $map;
        }

        $enrollments = Enrollment::getAll('student_id = ?', [$student->id]);

        foreach ($enrollments as $e) {
            if ($e->courseId !== null) {
                $map[$e->courseId] = $e->status ?? null;
            }
        }

        return $map;
    }
}
