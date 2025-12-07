<?php

namespace App\Controllers;

use App\Models\User as UserModel;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;


class AdminController extends BaseController
{
    // ZOBRAZÍ LOGIN FORM
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

        return null; // OK → povolené
    }
    // Unified users listing (students + teachers + others)
    public function pouzivatelia(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        // Load all users
        $users = User::getAllUsers();

        return $this->html(['users' => $users]);
    }



    public function kurzy(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;
        // Load all courses and pass them to the view
        $courses = Course::getAllCourses();

        return $this->html(['courses' => $courses]);
    }

    public function zapisy(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;
        // Look for enrollments that are not approved / pending variants
        $enrollments = Enrollment::getPendingEnrollments();

        return $this->html(['enrollments' => $enrollments]);
    }

    public function approveEnrollment(Request $request): Response
    {
        // Accept id via POST
        $id = $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('admin.zapisy'));
        }

        // Use model helper to approve
        Enrollment::approveById((int)$id);

        return $this->redirect($this->url('admin.zapisy'));
    }


    public function editUser(Request $request): Response
    {

        if ($resp = $this->requireAdmin()) return $resp;

        $requestedId = $request->get('id');
        if ($requestedId === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $userId = (int)$requestedId;
        $user = UserModel::findById($userId);
        if ($user === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        return $this->html(['userModel' => $user], 'editUser');
    }


    public function updateUser(Request $request): Response
    {
        $id = $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $user = UserModel::findById((int)$id);
        if ($user === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $data = [
            'firstName' => trim((string)$request->post('firstName')),
            'lastName' => trim((string)$request->post('lastName')),
            'email' => trim((string)$request->post('email')),
            'password' => $request->post('password'),
            'passwordConfirm' => $request->post('passwordConfirm'),
        ];

        // extra fields
        $studentNumber = trim((string)$request->post('studentNumber'));
        $year = $request->post('year');
        $department = trim((string)$request->post('department'));

        $errors = $user->updateProfile($data);



        // If there are errors, pass posted extra values back to the view so the form preserves them
        $studentData = ['studentNumber' => $studentNumber, 'year' => $year];
        $teacherData = ['department' => $department];

        if (!empty($errors)) {
            return $this->html(['userModel' => $user, 'errors' => $errors, 'studentData' => $studentData, 'teacherData' => $teacherData], 'editUser');
        }

        // Save or update student record if applicable
        // Only allow editing extra fields if admin or owner (we already enforced this above)
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

        // If any errors accumulated from student/teacher updates, re-render
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

        // collect data
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

        // create user via model helper
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
            return $this->html(['errors'=>['Neočakovaná chyba pri vytváraní používateľa.'],'posted'=>$posted], 'createUser');
        }

        // create related records depending on role; if creation fails, delete the user and show errors
        if ($role === 'student') {
            $studentRes = Student::create($user->id, ['studentNumber'=>$studentNumber,'year'=>$year]);
            if (!empty($studentRes['errors'])) {
                // rollback
                try { $user->delete(); } catch (\Throwable $_) {}
                return $this->html(['errors'=>$studentRes['errors'],'posted'=>$posted], 'createUser');
            }
        } elseif ($role === 'teacher') {
            $teacherRes = Teacher::create($user->id, ['department'=>$department]);
            if (!empty($teacherRes['errors'])) {
                // rollback
                try { $user->delete(); } catch (\Throwable $_) {}
                return $this->html(['errors'=>$teacherRes['errors'],'posted'=>$posted], 'createUser');
            }
        }

        return $this->redirect($this->url('admin.pouzivatelia'));
    }

    // Delete a user (admin only)
    public function deleteUser(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $id = $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $user = UserModel::findById((int)$id);
        if ($user === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        try {
            $user->delete();
        } catch (\Throwable $e) {
            // If deletion fails, re-render the list with an error
            $users = UserModel::getAllUsers();
            return $this->html(['users' => $users, 'errors' => ['Chyba pri mazaní používateľa: ' . $e->getMessage()]], 'pouzivatelia');
        }

        return $this->redirect($this->url('admin.pouzivatelia'));
    }

}
