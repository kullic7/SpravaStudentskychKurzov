<?php

namespace App\Controllers;

use App\Models\User as UserModel;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;


class AdminController extends BaseController
{
    public function index(Request $request): Response
    {
        return $this->html();
    }

    private function requireAdmin(): ?Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }
        try { $role = $appUser->getRole(); }
        catch (\Throwable $_) { $role = null; }

        if ($role !== 'admin') {
            return $this->redirect($this->url('auth.index'));
        }
        return null;
    }
    public function pouzivatelia(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }

        $session = $this->app->getSession();

        // flash errors
        $errors = $session->get('errors');
        $session->remove('errors');

        $users = User::getAllUsers();
        $students = Student::getAllStudents();
        $teachers = Teacher::getAllTeachers();

        $studentsByUser = [];
        foreach ($students as $s) {
            if (isset($s->userId)) {
                $studentsByUser[$s->userId] = $s;
            }
        }

        $teachersByUser = [];
        foreach ($teachers as $t) {
            if (isset($t->userId)) {
                $teachersByUser[$t->userId] = $t;
            }
        }

        return $this->html([
            'users' => $users,
            'studentsByUser' => $studentsByUser,
            'teachersByUser' => $teachersByUser,
            'errors' => $errors,
        ], 'pouzivatelia');
    }


    public function approveEnrollment(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;
        $id = $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('enrollment.zapisy'));
        }

        Enrollment::approveById((int)$id);
        return $this->redirect($this->url('enrollment.zapisy'));
    }

    public function editUser(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }

        $id = (int)$request->get('id');
        if (!$id) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $user = UserModel::findById($id);
        if (!$user) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $student = Student::findByUserId($user->id);
        $teacher = Teacher::findByUserId($user->id);

        return $this->html([
            'userModel' => $user,
            'student'   => $student,   // môže byť null
            'teacher'   => $teacher,   // môže byť null
        ], 'editUser');
    }

    //chat GPT doplnenie
    public function updateUser(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;
        $id = $request->post('id');
        $user = $id ? UserModel::findById((int)$id) : null;

        if (!$user) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $data = [
            'firstName' => trim((string)$request->post('firstName')),
            'lastName' => trim((string)$request->post('lastName')),
            'email' => trim((string)$request->post('email')),
            'password' => $request->post('password'),
            'passwordConfirm' => $request->post('passwordConfirm'),
        ];

        // student and teacher extra fields
        $studentNumber = trim((string)$request->post('studentNumber'));
        $year = $request->post('year');
        $department = trim((string)$request->post('department'));

        $errors = $user->updateProfile($data, true);

        // If there are errors, pass posted extra values back to the view so the form preserves them
        $studentData = ['studentNumber' => $studentNumber, 'year' => $year];
        $teacherData = ['department' => $department];

        if (!empty($errors)) {
            return $this->html(['userModel' => $user, 'errors' => $errors, 'studentData' => $studentData, 'teacherData' => $teacherData], 'editUser');
        }

        $student = Student::findByUserId($user->id);
        if ($student !== null) {
            try {
                $studentErrors = $student->update(['studentNumber' => $studentNumber, 'year' => $year]);
                if (!empty($studentErrors)) {
                    $errors = array_merge($errors, $studentErrors);
                }
            } catch (\Throwable $e) {
                $errors[] = 'Chyba pri ukladaní študenta: ' . $e->getMessage();
            }
        }

        // Teacher
        $teacher = Teacher::findByUserId($user->id);
        if ($teacher !== null) {
            try {
                $teacherErrors = $teacher->update(['department' => $department]);
                if (!empty($teacherErrors)) {
                    $errors = array_merge($errors, $teacherErrors);
                }
            } catch (\Throwable $e) {
                $errors[] = 'Chyba pri ukladaní učiteľa: ' . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return $this->html(['userModel' => $user, 'errors' => $errors, 'studentData' => $studentData, 'teacherData' => $teacherData], 'editUser');
        }

        return $this->redirect($this->url('admin.pouzivatelia'));
    }

    public function createUser(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        return $this->html([], 'createUser');
    }


    public function createUserPost(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $firstName = trim((string)$request->post('firstName'));
        $lastName = trim((string)$request->post('lastName'));
        $email = trim((string)$request->post('email'));
        $password = $request->post('password');
        $passwordConfirm = $request->post('passwordConfirm');
        $role = trim((string)$request->post('role'));

        $studentNumber = trim((string)$request->post('studentNumber'));
        $year = $request->post('year');
        $department = trim((string)$request->post('department'));

        $posted = ['firstName'=>$firstName,'lastName'=>$lastName,'email'=>$email,'role'=>$role,'studentNumber'=>$studentNumber,'year'=>$year,'department'=>$department];

        $userRes = UserModel::create([
            'firstName'=>$firstName,
            'lastName'=>$lastName,
            'email'=>$email,
            'password'=>$password,
            'passwordConfirm'=>$passwordConfirm,
            'role'=>$role,
        ]);

        if (!empty($userRes['errors'])) {
            return $this->html(['errors'=>$userRes['errors'],'posted'=>$posted], 'createUser');
        }

        $user = $userRes['user'];
        if ($user === null) {
            return $this->html(['errors'=>['Neočakavaná chyba pri vytváraní používateľa.'],'posted'=>$posted], 'createUser');
        }

        if ($role === 'student') {
            $studentRes = Student::create($user->id, ['studentNumber'=>$studentNumber,'year'=>$year]);
            if (!empty($studentRes['errors'])) {
                try { $user->delete(); } catch (\Throwable $_) {}
                return $this->html(['errors'=>$studentRes['errors'],'posted'=>$posted], 'createUser');
            }
        } elseif ($role === 'teacher') {
            $teacherRes = Teacher::create($user->id, ['department'=>$department]);
            if (!empty($teacherRes['errors'])) {
                try { $user->delete(); } catch (\Throwable $_) {}
                return $this->html(['errors'=>$teacherRes['errors'],'posted'=>$posted], 'createUser');
            }
        }

        return $this->redirect($this->url('admin.pouzivatelia'));
    }

    public function createCourse(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $teachers = Teacher::getAllTeachers();

        return $this->html(['teachers' => $teachers], 'createCourse');
    }

    public function createCoursePost(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $posted = [
            'name' => trim((string)$request->post('name')),
            'description' => trim((string)$request->post('description')),
            'credits' => $request->post('credits'),
            'teacherId' => $request->post('teacherId'),
        ];

        $res = Course::create($posted);
        if (!empty($res['errors'])) {
            $teachers = Teacher::getAllTeachers();
            return $this->html(['errors' => $res['errors'], 'posted' => $posted, 'teachers' => $teachers], 'createCourse');
        }

        return $this->redirect($this->url('course.kurzy'));
    }

    public function editCourse(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $course = Course::findById((int)$request->get('id'));
        if (!$course) {
            return $this->redirect($this->url('course.kurzy'));
        }

        $teachers = Teacher::getAllTeachers();

        return $this->html(['course' => $course, 'teachers' => $teachers], 'editCourse');
    }

    public function updateCoursePost(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $course = Course::findById((int)$request->post('id'));

        if (!$course) {
            return $this->redirect($this->url('course.kurzy'));
        }

        $posted = [
            'name' => trim((string)$request->post('name')),
            'description' => trim((string)$request->post('description')),
            'credits' => $request->post('credits'),
            'teacherId' => $request->post('teacherId'),
        ];

        try {
            $errors = $course->update($posted);
        } catch (\Throwable $e) {
            $errors = ['Chyba pri ukladaní kurzu: ' . $e->getMessage()];
        }

        if (!empty($errors)) {
            $teachers = Teacher::getAllTeachers();
            return $this->html(['errors' => $errors, 'posted' => $posted, 'course' => $course, 'teachers' => $teachers], 'editCourse');
        }

        return $this->redirect($this->url('course.kurzy'));
    }

    public function deleteCourse(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $course = Course::findById((int)$request->post('id'));

        if (!$course) {
            return $this->redirect($this->url('course.kurzy'));
        }

        try {
            $course->delete();
        } catch (\Throwable $e) {
            try {
                $this->app->getSession()->set('errors', ['Chyba pri mazaní kurzu: ' . $e->getMessage()]);
            } catch (\Throwable $_) {
                // ignore session errors
            }

            return $this->redirect($this->url('course.kurzy'));
        }

        return $this->redirect($this->url('course.kurzy'));
    }

    public function deleteUser(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $user = UserModel::findById((int)$request->post('id'));

        if (!$user) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        try {
            $user->delete();
        } catch (\Throwable $e) {
            try {
                $this->app->getSession()->set(
                    'errors',
                    ['Chyba pri mazaní používateľa.']
                );
            } catch (\Throwable $_) {}
        }

        return $this->redirect($this->url('admin.pouzivatelia'));
    }
}
