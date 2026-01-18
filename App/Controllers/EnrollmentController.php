<?php

namespace App\Controllers;

use App\Models\Enrollment;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Student;

class EnrollmentController extends BaseController
{
    // default index -> redirect to zapisy
    public function index(Request $request): Response
    {
        return $this->html();
    }

    public function zapisy(Request $request): Response
    {
        $user = $this->app->getAuthenticator()->getUser();
        if (!$user) {
            return $this->redirect($this->url('auth.index'));
        }

        $role = $user->getRole();

        // 1️⃣ načítanie zápisov podľa roly
        switch ($role) {
            case 'admin':
                $enrollments = Enrollment::getPendingEnrollments();
                break;

            case 'student':
                $enrollments = $this->getStudentPendingEnrollments($user);
                break;

            default:
                $enrollments = [];
        }

        $rows = [];

        foreach ($enrollments as $en) {
            $student = $en->getStudent();
            $studentUser = $student ? $student->getUser() : null;
            $course = $en->getCourse();

            $rows[] = [
                'id' => $en->id,
                'studentName' => $studentUser
                    ? trim(($studentUser->firstName ?? '') . ' ' . ($studentUser->lastName ?? ''))
                    : '-',
                'studentEmail' => $studentUser->email ?? '-',
                'courseName' => $course->name ?? '-',
                'status' => $en->status,
            ];
        }
        return $this->html([
            'rows' => $rows,
            'user' => $user,
        ], 'zapisy');
    }


    private function getStudentPendingEnrollments($user): array
    {
        $userId = $user->getId();
        if (!$userId) {
            return [];
        }

        $student = Student::findByUserId((int)$userId);
        if (!$student || !$student->id) {
            return [];
        }

        return Enrollment::getPendingByStudentId($student->id);
    }

}
