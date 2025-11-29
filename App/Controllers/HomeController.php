<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class HomeController extends BaseController
{
    // Dashboard - shows different content depending on user's role
    public function index(Request $request): Response
    {
        return $this->html([], 'login');
    }
}
