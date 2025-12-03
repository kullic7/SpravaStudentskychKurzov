<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\User as UserModel;
use App\Models\LoggedUser;
use App\Models\Student;
use App\Models\Teacher;


class AuthController extends BaseController
{
    // ZOBRAZÍ LOGIN FORM
    public function index(Request $request): Response
    {
        return $this->html([], "login");
        // toto hľadá súbor App/Views/Auth/login.view.php
    }

    // SPRACOVANIE FORMULÁRA
    public function login(Request $request): Response
    {
        $logged = null;
        if ($request->hasValue('submit')) {
            $logged = $this->app->getAuthenticator()->login($request->value('email'), $request->value('password'));
            if ($logged) {
                return $this->redirect($this->url("home.index"));

            }
        }
        // NEÚSPEŠNÉ PRIHLÁSENIE ALEBO PRVÉ ZOBRAZENIE
        return $this->html([
            'error' => $logged === false ? 'Bad username or password' : null
        ], 'login');

    }

    // ODHLÁSENIE AKTUÁLNEHO UŽÍVATEĽA
    public function logout(Request $request): Response
    {
        $auth = $this->app->getAuthenticator();
        if ($auth !== null) {
            $auth->logout();
        } else {
            // If no authenticator is configured, attempt to clear session as a fallback
            try {
                $this->app->getSession()->destroy();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Redirect to the login page (show login form)
        return $this->redirect($this->url('auth.index'));
    }

    // ZOBRAZI PROFILE (spoločný pre všetky role)
    public function profile(Request $request): Response
    {
        // Ensure user is logged in
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        // allow optional id param to view another user's profile (admins only)
        $requestedId = $request->get('id');
        $viewerRole = null;
        try { $viewerRole = $appUser->getRole(); } catch (\Throwable $_) { $viewerRole = null; }

        if ($requestedId !== null) {
            // if admin, allow viewing others
            if ($viewerRole === 'admin') {
                $userId = (int)$requestedId;
            } else {
                // non-admins may only view their own profile
                $userId = $appUser->getId();
            }
        } else {
            $userId = $appUser->getId();
        }

        $user = UserModel::findById($userId);
        if ($user === null) {
            return $this->redirect($this->url('home.index'));
        }

        return $this->html(['userModel' => $user], 'profile');
    }

    // SPRACOVANIE UPRAVY PROFILE
    public function updateProfile(Request $request): Response
    {
        // Ensure user is logged in
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            // For AJAX request return JSON error
            if ($request->isAjax()) {
                return new JsonResponse(['success' => false, 'errors' => ['Not authenticated']]);
            }
            return $this->redirect($this->url('auth.index'));
        }

        $userId = $appUser->getId();
        $user = UserModel::findById($userId);
        if ($user === null) {
            if ($request->isAjax()) {
                return new JsonResponse(['success' => false, 'errors' => ['User not found']]);
            }
            return $this->redirect($this->url('auth.index'));
        }

        // Build data array from submitted values
        $data = [
            'firstName' => trim((string)$request->post('firstName')),
            'lastName' => trim((string)$request->post('lastName')),
            'email' => trim((string)$request->post('email')),
            'password' => $request->post('password'),
            'passwordConfirm' => $request->post('passwordConfirm'),
        ];

        // Delegate validation and saving to the model helper
        $errors = $user->updateProfile($data);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['success' => false, 'errors' => $errors]);
            }
            return $this->html(['userModel' => $user, 'errors' => $errors], 'profile');
        }

        // Update session identity so the logged-user info reflects changes immediately
        try {
            $newIdentity = new LoggedUser(
                $user->id,
                $user->email ?? '',
                $user->firstName ?? '',
                $user->lastName ?? '',
                $user->role ?? ''
            );
            // Session key used by SessionAuthenticator
            $this->app->getSession()->set('fw.session.user.identity', $newIdentity);

            // Refresh the controller's user instance so templates rendered in this request see the change
            $this->user = $this->app->getAppUser();
        } catch (\Throwable $e) {
            // ignore session update failures - the DB was still updated
        }

        // After save, respond according to request type
        if ($request->isAjax()) {
            // Optionally, return updated user data (avoid sensitive fields)
            return new JsonResponse([
                'success' => true,
                'message' => 'Profil uložený',
                'user' => [
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        }

        return $this->redirect($this->url('auth.profile'));
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
