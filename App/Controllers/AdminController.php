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
        // Load all users
        $users = User::getAllUsers();

        return $this->html(['users' => $users]);
    }



    public function kurzy(Request $request): Response
    {
        // Load all courses and pass them to the view
        $courses = Course::getAllCourses();

        return $this->html(['courses' => $courses]);
    }

    public function zapisy(Request $request): Response
    {
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

        $errors = $user->updateProfile($data);

        // Allow admins to change the role
        if ($viewerRole === 'admin') {
            $role = trim((string)$request->post('role'));
            if ($role !== '') {
                $user->role = $role;
                $user->save();
            }
        }

        if (!empty($errors)) {
            return $this->html(['userModel' => $user, 'errors' => $errors], 'editUser');
        }

        return $this->redirect($this->url('admin.pouzivatelia'));
    }


}
