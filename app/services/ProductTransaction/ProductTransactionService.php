<?php

namespace App\Services\ProductTransaction;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductTransactionService
{
    public function getPaginatedTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::with([
            'customer.user:id,name,email,photo,role',
            'driver.user:id,name,email,photo,role',
            'details.product:id,name,photo',
        ]);

        // Filter by order status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by payment method
        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Filter by search (customer name or delivery address)
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('delivery_address', 'like', "%{$search}%")
                    ->orWhereHas('customer.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }
}
