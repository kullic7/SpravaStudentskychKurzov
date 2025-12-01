<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\User as UserModel;


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

        $userId = $appUser->getId();
        $user = UserModel::findById($userId);

        return $this->html(['userModel' => $user], 'profile');
    }

    // SPRACOVANIE UPRAVY PROFILE
    public function updateProfile(Request $request): Response
    {
        // Ensure user is logged in
        $appUser = $this->app->getAuthenticator()->getUser();
        if (!$appUser->isLoggedIn()) {
            return $this->redirect($this->url('auth.index'));
        }

        $userId = $appUser->getId();
        $user = UserModel::findById($userId);
        if ($user === null) {
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
            return $this->html(['userModel' => $user, 'errors' => $errors], 'profile');
        }

        // After save, redirect back to profile (could add flash message)
        return $this->redirect($this->url('auth.profile'));
    }

}
