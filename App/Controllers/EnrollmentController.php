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

    /**
     * Prepare enrollments depending on role:
     * - admin: all enrollments with pending/not-approved statuses
     * - student: all pending enrollments belonging to the logged student
     */
    public function zapisy(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser || !$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $role = $appUser->getRole(); } catch (\Throwable $_) { $role = null; }

        // default pending statuses used by the model helper
        $statuses = ['not_approved', 'not approved', 'not aproved', 'pending'];

        if ($role === 'admin') {
            // admin sees all pending enrollments
            $enrollments = Enrollment::getPendingEnrollments();
        } else if ($role === 'student') {
            // student sees only their pending enrollments
            $userId = $appUser->getId();
            if ($userId === null) {
                return $this->redirect($this->url('auth.index'));
            }

            // map user id -> student id (enrollments.student_id references students.id)
            $student = Student::findByUserId((int)$userId);
            if ($student === null) {
                // No student record for this user → nothing to show
                $enrollments = [];
            } else {
                $studentId = $student->id;
                // build placeholders for statuses
                $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
                $where = "student_id = ? AND status IN ($placeholders)";
                $params = array_merge([$studentId], $statuses);
                $enrollments = Enrollment::getAll($where, $params);
            }
        } else {
            // other roles: no enrollments
            $enrollments = [];
        }

        return $this->html(['enrollments' => $enrollments]);
    }

}
