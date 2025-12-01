<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;

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
                $studentCount = Student::getCount();
                $teacherCount = Teacher::getCount();

                return $this->html([
                    'courseCount' => $courseCount,
                    'studentCount' => $studentCount,
                    'teacherCount' => $teacherCount
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
