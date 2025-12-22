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
    // Action that shows the shared courses view for admin and student
    public function kurzy(Request $request): Response
    {
        // Prepare courses
        $courses = Course::getAllCourses();

        // Precompute teachers for each course
        $courseTeachers = [];
        foreach ($courses as $c) {
            $teachersForCourse = [];
            if (!empty($c->teacherId)) {
                $t = Teacher::findById($c->teacherId);
                if ($t !== null) {
                    $u = $t->getUser();
                    $teachersForCourse[] = (object)[
                        'teacher' => $t,
                        'user' => $u,
                        'name' => $u ? ($u->firstName . ' ' . $u->lastName) : null,
                        'email' => $u ? $u->email : null,
                    ];
                }
            }
            $courseTeachers[$c->id] = $teachersForCourse;
        }

        // flat list of teachers for selects (may be used by admin-only UI)
        $allTeachers = Teacher::getAllTeachers();

        // Decide permissions based on current user's role
        $appUser = $this->app->getAuthenticator()->getUser();

        try { $role = $appUser->getRole(); } catch (\Throwable $_) { $role = null; }

        $isAdmin = ($role === 'admin');
        $isStudent = ($role === 'student');

        // Prepare a map of student's enrollments to avoid DB calls from view
        $studentEnrollmentsMap = [];
        if ($isStudent && $appUser && $appUser->getId() !== null) {
            try {
                $studentModel = Student::findByUserId((int)$appUser->getId());
                if ($studentModel !== null && $studentModel->id !== null) {
                    $ens = Enrollment::getAll('student_id = ?', [$studentModel->id]);
                    foreach ($ens as $e) {
                        if ($e->courseId !== null) {
                            $studentEnrollmentsMap[$e->courseId] = $e->status ?? null;
                        }
                    }
                }
            } catch (\Throwable $_) {
                // ignore and leave map empty
                $studentEnrollmentsMap = [];
            }
        }

        // The shared view can use these flags to show/hide buttons and actions
        return $this->html([
            'courses' => $courses,
            'courseTeachers' => $courseTeachers,
            'allTeachers' => $allTeachers,
            'isAdmin' => $isAdmin,
            'isStudent' => $isStudent,
            'studentEnrollmentsMap' => $studentEnrollmentsMap,
        ], 'kurzy');
    }
}
