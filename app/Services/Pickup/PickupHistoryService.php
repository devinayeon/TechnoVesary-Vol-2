<?php

namespace App\Services\Pickup;

use App\Models\WasteDeposit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PickupHistoryService
{
    public function getPaginatedPickups(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WasteDeposit::with([
            'customer.user:id,name,email,photo,role',
            'driver.user:id,name,email,photo,role',
            'details.wasteCategory:id,name',
        ]);

        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by search (customer name, driver name, or address)
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('address', 'like', "%{$search}%")
                    ->orWhereHas('customer.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }
}
