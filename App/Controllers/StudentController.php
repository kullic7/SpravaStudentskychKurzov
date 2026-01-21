<?php
namespace App\Controllers;
use App\Models\Enrollment;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Student;

class StudentController extends BaseController
{
    public function index(Request $request): Response
    {
        return $this->html();
    }

    public function cancelEnrollment(Request $request): Response
    {
        $user = $this->app->getAuthenticator()->getUser();
        if (!$user || $user->getRole() !== 'student') {
            return $this->redirect($this->url('auth.index'));
        }

        $enrollmentId = $request->post('id');
        if (!$enrollmentId) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        $enrollment = Enrollment::getOne((int)$enrollmentId);
        if (!$enrollment) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        $student = Student::findByUserId((int)$user->getId());
        if (!$student || !$student->id) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        if ($enrollment->studentId !== $student->id) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        $enrollment->delete();

        return $this->redirect($this->url('enrollment.zapisy'));
    }

    public function zapis(Request $request): Response
    {
        $user = $this->app->getAuthenticator()->getUser();
        if (!$user || $user->getRole() !== 'student') {
            return $this->redirect($this->url('auth.index'));
        }

        $courseId = $request->post('courseId');
        if (!$courseId) {
            return $this->redirect($this->url('course.kurzy'));
        }

        $student = Student::findByUserId((int)$user->getId());
        if (!$student || !$student->id) {
            return $this->redirect($this->url('course.kurzy'));
        }

        $result = Enrollment::create(
            $student->id,
            (int)$courseId,
            ['status' => 'not_approved']
        );

        return $this->redirect($this->url('course.kurzy'));
    }
}
