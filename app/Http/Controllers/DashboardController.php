<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class DashboardController extends Controller {
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Display dashboard
     */
    public function index() {
        $totalUsers = $this->userService->getAll()->count();

        return view('dashboard.index', compact('totalUsers'));
    }
}
