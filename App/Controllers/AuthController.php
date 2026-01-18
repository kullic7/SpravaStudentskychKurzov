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
    }

    public function login(Request $request): Response
    {
        $logged = null;
        if ($request->hasValue('submit')) {
            $logged = $this->app->getAuthenticator()->login($request->value('email'), $request->value('password'));
            if ($logged) {
                return $this->redirect($this->url("home.index"));

            }
        }

        return $this->html([
            'error' => $logged === false ? 'Bad username or password' : null
        ], 'login');

    }

    public function logout(Request $request): Response
    {
        $auth = $this->app->getAuthenticator();
        if ($auth !== null) {
            $auth->logout();
        } else {
            try {
                $this->app->getSession()->destroy();
            } catch (\Throwable $e) {
            }
        }

        // Redirect to the login page (show login form)
        return $this->redirect($this->url('auth.index'));
    }

    public function profile(Request $request): Response
    {
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser || !$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        $requestedId = $request->get('id');
        $userId = $appUser->getId();

        if ($requestedId !== null && $appUser->getRole() === 'admin') {
            $userId = (int) $requestedId;
        }

        $user = UserModel::findById($userId);
        if (!$user) {
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

        $this->refreshSessionIdentity($user);

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

    //helper metod
    private function refreshSessionIdentity(UserModel $user): void
    {
        try {
            $identity = new LoggedUser(
                $user->id,
                $user->email ?? '',
                $user->firstName ?? '',
                $user->lastName ?? '',
                $user->role ?? ''
            );

            $this->app->getSession()->set(
                'fw.session.user.identity',
                $identity
            );

            $this->user = $this->app->getAppUser();
        } catch (\Throwable $_) {
            // ignore
        }
    }
}
