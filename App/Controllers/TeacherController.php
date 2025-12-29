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

    /**
     * Show list of students that are enrolled in any course taught by the logged-in teacher.
     */
    public function studenti(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser || !$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $role = $appUser->getRole(); } catch (\Throwable $_) { $role = null; }
        if ($role !== 'teacher') {
            // not a teacher -> redirect to home or login
            return $this->redirect($this->url('auth.index'));
        }

        $userId = $appUser->getId();
        if ($userId === null) {
            return $this->redirect($this->url('auth.index'));
        }

        $teacher = Teacher::findByUserId((int)$userId);
        if ($teacher === null) {
            // no teacher record
            return $this->html(['students' => [], 'teacher' => null]);
        }

        // find courses taught by this teacher
        $courses = Course::findByTeacherId((int)$teacher->id);
        $coursesById = [];
        $courseIds = [];
        foreach ($courses as $c) {
            $coursesById[$c->id] = $c;
            $courseIds[] = $c->id;
        }

        $studentsMap = []; // studentId => ['student' => Student, 'user' => User, 'courses' => [cid => ['course'=>Course,'enrollment'=>Enrollment],...]]

        if (!empty($courseIds)) {
            // build placeholders
            $placeholders = implode(', ', array_fill(0, count($courseIds), '?'));
            $params = $courseIds;
            // only include enrollments that are approved (teacher likely interested in actual enrolled students)
            $where = "course_id IN ($placeholders) AND status = ?";
            $params[] = 'approved';

            $enrollments = Enrollment::getAll($where, $params);

            foreach ($enrollments as $en) {
                $sid = $en->studentId;
                if ($sid === null) continue;
                if (!isset($studentsMap[$sid])) {
                    $st = Student::findById((int)$sid);
                    if ($st === null) continue;
                    $studentsMap[$sid] = [
                        'student' => $st,
                        'user' => $st->getUser(),
                        'courses' => [],
                    ];
                }
                $cid = $en->courseId;
                if ($cid !== null && isset($coursesById[$cid])) {
                    // store both course and the enrollment so view can show grade
                    $studentsMap[$sid]['courses'][$cid] = [
                        'course' => $coursesById[$cid],
                        'enrollment' => $en,
                    ];
                }
            }
        }

        // convert map to indexed array
        $students = array_values($studentsMap);

        return $this->html(['students' => $students, 'teacher' => $teacher, 'courses' => $courses]);
    }

    /**
     * Update grade for an enrollment. Only allowed if logged-in teacher actually teaches the course.
     */
    public function updateGrade(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser || !$appUser->isLoggedIn()) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Nie ste prihlásený.']);
            }
            return $this->redirect($this->url('auth.index'));
        }

        try { $role = $appUser->getRole(); } catch (\Throwable $_) { $role = null; }
        if ($role !== 'teacher') {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Nemáte oprávnenie.']);
            }
            return $this->redirect($this->url('auth.index'));
        }

        $userId = $appUser->getId();
        if ($userId === null) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Neznámy používateľ.']);
            }
            return $this->redirect($this->url('auth.index'));
        }

        $teacher = Teacher::findByUserId((int)$userId);
        if ($teacher === null) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Nenašiel sa učiteľ.']);
            }
            return $this->redirect($this->url('auth.index'));
        }

        $enrollmentId = $request->post('enrollmentId');
        $grade = $request->post('grade');

        if ($enrollmentId === null) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Chýba ID zápisu.']);
            }
            return $this->redirect($this->url('teacher.studenti'));
        }


        // delegate update to model helper
        $res = Enrollment::updateZnamky((int)$enrollmentId, $grade);

        if ($request->isAjax()) {
            if (!empty($res['success'])) {
                return $this->json(['success' => true, 'grade' => $res['grade'] ?? null]);
            }
            return $this->json(['success' => false, 'message' => $res['message'] ?? 'Chyba pri ukladaní.']);
        }

        // non-ajax fallback: redirect back
        return $this->redirect($this->url('teacher.studenti'));
    }

}
