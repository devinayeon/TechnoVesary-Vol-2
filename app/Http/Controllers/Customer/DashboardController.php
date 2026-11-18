<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard ringkasan untuk nasabah.
     */
    public function index(): Response
    {
        return Inertia::render('Customer/Dashboard');
    }
}
