<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService
    ) {}

    /**
     * Menampilkan overview dashboard operasional admin dengan data riil dari database.
     */
    public function index(): Response
    {
        $dashboardData = $this->dashboardService->getDashboardData();

        return Inertia::render('Admin/Dashboard', $dashboardData);
    }
}
