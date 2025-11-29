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
                //return $this->redirect($this->url("admin.index"));
                $user = $this->app->getAuthenticator()->getUser();
                $role = strtolower($user->getRole());

                switch ($role) {
                    case 'admin':
                        return $this->redirect('?c=admin&a=index');
                    case 'teacher':
                        return $this->redirect('?c=teacher&a=index');
                    case 'student':
                        return $this->redirect('?c=student&a=index');
                    default:
                        return $this->redirect('?c=home&a=index');
                }
            }
        }
        // NEÚSPEŠNÉ PRIHLÁSENIE ALEBO PRVÉ ZOBRAZENIE
        return $this->html([
            'error' => $logged === false ? 'Bad username or password' : null
        ], 'login');

    }

}
