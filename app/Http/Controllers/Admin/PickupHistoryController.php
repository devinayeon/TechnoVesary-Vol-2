<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PickupStatus;
use App\Http\Controllers\Controller;
use App\Services\Pickup\PickupHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PickupHistoryController extends Controller
{
    public function __construct(
        private readonly PickupHistoryService $service
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);

        $pickups = $this->service->getPaginatedPickups($filters, 15);

        $statuses = array_map(
            fn (PickupStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            PickupStatus::cases()
        );

        return Inertia::render('Admin/PickupHistory/Index', [
            'pickups' => $pickups,
            'filters' => $filters,
            'statuses' => $statuses,
        ]);
    }
}
