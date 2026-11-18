<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard antrean tugas untuk driver.
     */
    public function index(): Response
    {
        return Inertia::render('Driver/Dashboard');
    }
}
