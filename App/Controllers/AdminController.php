<?php

namespace App\Controllers;

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



}
