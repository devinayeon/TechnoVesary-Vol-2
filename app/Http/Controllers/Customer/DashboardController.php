<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Services\Dashboard\CustomerDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly CustomerDashboardService $dashboardService
    ) {}

    /**
     * Menampilkan dashboard ringkasan untuk nasabah dengan data fungsional dari database.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);
        $dashboardData = $this->dashboardService->getDashboardData($customer);

        return Inertia::render('Customer/Dashboard', $dashboardData);
    }
}
