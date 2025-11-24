<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

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
        if ($request->isPost()) {

            $email = $request->getValue('email');
            $password = $request->getValue('password');

            $result = $this->app->getAuthenticator()->login($email, $password);

            if ($result) {
                return $this->redirect('/');  // po login úspešný redirect
            }

            return $this->html(['error' => 'Nesprávne údaje'], "Auth/login");
        }

        return $this->redirect('?c=auth');
    }
}
