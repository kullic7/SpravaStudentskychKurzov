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
        $enrollments = [];

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

        return $this->html([
            'enrollments' => $enrollments
        ]);
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
