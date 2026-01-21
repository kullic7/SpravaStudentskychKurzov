<?php

namespace App\Controllers;

use App\Models\Enrollment;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;

class HomeController extends BaseController
{
    public function index(Request $request): Response
    {
        $user = $this->app->getAuthenticator()->getUser();
        if (!$user || !$user->isLoggedIn()) {
            return $this->redirect('?c=auth&a=login');
        }

        return match (strtolower($user->getRole())) {
            'admin'   => $this->html($this->prepareAdminData(), 'admin'),
            'teacher' => $this->html($this->prepareTeacherData($user), 'teacher'),
            'student' => $this->html($this->prepareStudentData($user), 'student'),
            default   => $this->redirect('?c=auth&a=login'),
        };
    }

    private function prepareAdminData(): array
    {
        return [
            'courseCount' => Course::getCount(),
            'userCount' => User::getCount(),
            'enrollmentCount' => Enrollment::getPendingCount(),
        ];
    }

    private function prepareTeacherData($user): array
    {
        $totalCourses = 0;
        $studentsCount = 0;
        $teacherCourses = [];

        try {
            $teacher = Teacher::findByUserId($user->getId());
            if (!$teacher || !$teacher->id) {
                return compact('totalCourses', 'studentsCount', 'teacherCourses');
            }

            $courses = Course::findByTeacherId($teacher->id);
            $totalCourses = count($courses);

            $uniqueStudentIds = [];

            foreach ($courses as $c) {
                $studentCount = Enrollment::getCount(
                    'course_id = ? AND status = ?',
                    [$c->id, 'approved']
                );

                $avg = Enrollment::averageGradeByCourse($c->id);

                $enrollments = Enrollment::getAll(
                    'course_id = ? AND status = ?',
                    [$c->id, 'approved']
                );

                foreach ($enrollments as $e) {
                    if (!empty($e->studentId)) {
                        $uniqueStudentIds[(int)$e->studentId] = true;
                    }
                }

                $teacherCourses[] = [
                    'courseId' => $c->id,
                    'name' => $c->name,
                    'credits' => $c->credits,
                    'studentCount' => $studentCount,
                    'averageGrade' => $avg,
                ];
            }

            $studentsCount = count($uniqueStudentIds);
        } catch (\Throwable $_) {

        }
        return compact('totalCourses', 'studentsCount', 'teacherCourses');
    }

    private function prepareStudentData($user): array
    {
        $totalCourses = 0;
        $pendingEnrollments = 0;
        $averageGrade = null;
        $studentEnrollments = [];

        try {
            $student = Student::findByUserId($user->getId());
            if (!$student || !$student->id) {
                return [
                    'totalCourses' => $totalCourses,
                    'pendingEnrollments' => $pendingEnrollments,
                    'averageGrade' => $averageGrade,
                    'enrollments' => $studentEnrollments,
                ];
            }

            $totalCourses = Enrollment::countByStudent($student->id);
            $pendingEnrollments = Enrollment::pendingCountByStudent($student->id);
            $averageGrade = Enrollment::averageGradeByStudent($student->id);

            $enrollments = Enrollment::getAll(
                'student_id = ? AND status = ?',
                [$student->id, 'approved']
            );

            foreach ($enrollments as $e) {
                $course = $e->getCourse();

                $studentEnrollments[] = [
                    'courseId' => $course?->id ?? null,
                    'courseName' => $course?->name ?? '-',
                    'teacherName' => $course?->getTeacher()?->getUser()?->getName() ?? '-',
                    'description' => $course?->description ?? null,
                    'credits' => $course?->credits ?? null,
                    'grade' => $e->grade ?? null,
                ];
            }
        } catch (\Throwable $_) {

        }
        return [
            'totalCourses' => $totalCourses,
            'pendingEnrollments' => $pendingEnrollments,
            'averageGrade' => $averageGrade,
            'enrollments' => $studentEnrollments,
        ];
    }
}
