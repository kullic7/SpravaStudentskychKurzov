<?php

namespace App\Controllers;

use App\Models\Enrollment;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Student;

class StudentController extends BaseController
{
    // default index -> redirect to zapisy
    public function index(Request $request): Response
    {
        return $this->html();
    }


    /**
     * Cancel (unenroll) an enrollment. Only the owning student may cancel their enrollment.
     */
    public function cancelEnrollment(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser || !$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $role = $appUser->getRole(); } catch (\Throwable $_) { $role = null; }

        // only students may cancel via this endpoint
        if ($role !== 'student') {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        $id = $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        $en = Enrollment::getOne((int)$id);
        if ($en === null) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        // map user id -> student id (enrollments.student_id references students.id)
        $userId = $appUser->getId();
        if ($userId === null) {
            return $this->redirect($this->url('auth.index'));
        }
        $student = Student::findByUserId((int)$userId);
        if ($student === null) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }
        $studentId = $student->id;

        // ensure the enrollment belongs to this student
        if ($en->studentId !== $studentId) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        try {
            $en->delete();
        } catch (\Throwable $e) {
            // ignore errors - redirect back (could augment to show error)
        }

        return $this->redirect($this->url('enrollment.zapisy'));
    }

    /**
     * Enroll the logged-in student to a course (create enrollment with status 'not_approved')
     * Only creates if the student doesn't already have an enrollment for that course.
     */
    public function zapis(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser || !$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $role = $appUser->getRole(); } catch (\Throwable $_) { $role = null; }

        // only students can enroll themselves here
        if ($role !== 'student') {
            return $this->redirect($this->url('course.kurzy'));
        }

        $courseId = $request->post('courseId');
        if ($courseId === null) {
            return $this->redirect($this->url('course.kurzy'));
        }

        $userId = $appUser->getId();
        if ($userId === null) {
            return $this->redirect($this->url('auth.index'));
        }

        // find student record (enrollments.student_id references students.id)
        $student = Student::findByUserId((int)$userId);
        if ($student === null) {
            // user is not a student
            return $this->redirect($this->url('course.kurzy'));
        }

        $studentId = $student->id;
        $courseIdInt = (int)$courseId;

        // delegate creation to the model helper, which checks duplicates and returns errors if any
        $res = Enrollment::create($studentId, $courseIdInt, ['status' => 'not_approved']);
        if (!empty($res['errors'])) {
            // If duplicate or other error occurred, just redirect back to courses list for now.
            // Optionally we could surface $res['errors'] to the view.
            return $this->redirect($this->url('course.kurzy'));
        }

        return $this->redirect($this->url('course.kurzy'));
    }
}
