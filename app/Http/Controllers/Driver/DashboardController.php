<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use App\Services\Dashboard\DriverDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DriverDashboardService $dashboardService
    ) {}

    /**
     * Menampilkan dashboard antrean tugas untuk driver dengan data riil dari database.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $driver = $user->driver;
        if (! ($driver instanceof Driver)) {
            $driver = new Driver;
            $driver->user_id = $user->id;
            $driver->is_available = true;
            $driver->save();
        }

        $dashboardData = $this->dashboardService->getDashboardData($driver);

        return Inertia::render('Driver/Dashboard', $dashboardData);
    }
}
