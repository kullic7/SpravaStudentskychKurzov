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

    // Unified users listing (students + teachers + others)
    public function pouzivatelia(Request $request): Response
    {
        // Ensure user is logged in and is an admin
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $viewerRole = $appUser->getRole(); } catch (\Throwable $_) { $viewerRole = null; }
        if ($viewerRole !== 'admin') {
            // Not an admin -> redirect to login (as requested)
            return $this->redirect($this->url('auth.index'));
        }

        // Load all users
        $users = User::getAllUsers();

        return $this->html(['users' => $users]);
    }



    public function kurzy(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $viewerRole = $appUser->getRole(); } catch (\Throwable $_) { $viewerRole = null; }
        if ($viewerRole !== 'admin') {
            // Not an admin -> redirect to login (as requested)
            return $this->redirect($this->url('auth.index'));
        }
        // Load all courses and pass them to the view
        $courses = Course::getAllCourses();

        return $this->html(['courses' => $courses]);
    }

    public function zapisy(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        try { $viewerRole = $appUser->getRole(); } catch (\Throwable $_) { $viewerRole = null; }
        if ($viewerRole !== 'admin') {
            // Not an admin -> redirect to login (as requested)
            return $this->redirect($this->url('auth.index'));
        }
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

    // ZOBRAZI FORMULÁR PRE UPRAVU INÉHO UŽÍVATEĽA (ADMIN alebo vlastný profil)
    public function editUser(Request $request): Response
    {
        // Ensure user is logged in
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        $requestedId = $request->get('id');
        if ($requestedId === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $userId = (int)$requestedId;
        try { $viewerRole = $appUser->getRole(); } catch (\Throwable $_) { $viewerRole = null; }

        // Non-admins may only edit their own profile
        if ($viewerRole !== 'admin' && $userId !== $appUser->getId()) {
            return $this->redirect($this->url('home.index'));
        }

        $user = UserModel::findById($userId);
        if ($user === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        return $this->html(['userModel' => $user], 'editUser');
    }

    // SPRACOVANIE ÚPRAVY UŽÍVATEĽA (ADMIN alebo vlastné)
    public function updateUser(Request $request): Response
    {
        // Ensure user is logged in
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        $id = $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        $user = UserModel::findById((int)$id);
        if ($user === null) {
            return $this->redirect($this->url('admin.pouzivatelia'));
        }

        try { $viewerRole = $appUser->getRole(); } catch (\Throwable $_) { $viewerRole = null; }
        if ($viewerRole !== 'admin' && $user->id !== $appUser->getId()) {
            return $this->redirect($this->url('home.index'));
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

        // Allow admins to change the role
        if ($viewerRole === 'admin') {
            $role = trim((string)$request->post('role'));
            if ($role !== '') {
                $user->role = $role;
                $user->save();
            }
        }

        // If there are errors, pass posted extra values back to the view so the form preserves them
        $studentData = ['studentNumber' => $studentNumber, 'year' => $year];
        $teacherData = ['department' => $department];

        if (!empty($errors)) {
            return $this->html(['userModel' => $user, 'errors' => $errors, 'studentData' => $studentData, 'teacherData' => $teacherData], 'editUser');
        }

        // Save or update student record if applicable
        // Only allow editing extra fields if admin or owner (we already enforced this above)
        $student = Student::findByUserId($user->id);
        if ($student === null) {
            if ($studentNumber !== '' || ($year !== null && $year !== '')) {
                $student = new Student();
                $student->userId = $user->id;
            }
        }
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
        if ($teacher === null) {
            if ($department !== '') {
                $teacher = new Teacher();
                $teacher->userId = $user->id;
            }
        }
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


}
