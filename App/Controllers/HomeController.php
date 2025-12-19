<?php

namespace App\Controllers;

use App\Models\Enrollment;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Course;
use App\Models\User;



class HomeController extends BaseController
{
    // Dashboard - shows different content depending on user's role
    public function index(Request $request): Response
    {
        //return $this->html([], 'login');
        $user = $this->app->getAuthenticator()->getUser();
        $role = strtolower($user->getRole());

        switch ($role) {
            case 'admin':
                // fetch counts from DB and pass to view
                $courseCount = Course::getCount();
                $userCount = User::getCount();
                $enrollmentCount = Enrollment::getPendingCount();

                return $this->html([
                    'courseCount' => $courseCount,
                    'userCount' => $userCount,
                    'enrollmentCount' => $enrollmentCount
                ], 'admin');
            case 'teacher':
                return $this->html([], 'teacher');
            case 'student':
                return $this->html([], 'student');
            default:
                return $this->redirect('?c=auth&a=login');
        }
    }
}
