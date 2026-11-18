<?php

namespace App\Services\Point;

use App\Enums\PointType;
use App\Models\Customer;
use App\Models\PointHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PointHistoryService
{
    /**
     * Riwayat mutasi poin milik satu customer.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getCustomerHistories(Customer $customer, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return PointHistory::query()
            ->where('customer_id', $customer->id)
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $search = trim((string) $search);

                $query->where(function ($inner) use ($search) {
                    $inner->where('source', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $inner->orWhere('amount', (int) $search);
                    }
                });
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ringkasan poin masuk, poin keluar, dan saldo saat ini.
     *
     * @return array{total_in: int, total_out: int, balance: int}
     */
    public function getCustomerSummary(Customer $customer): array
    {
        $totals = PointHistory::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as total_in', [PointType::IN->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as total_out', [PointType::OUT->value])
            ->first();

        return [
            'total_in' => (int) ($totals->total_in ?? 0),
            'total_out' => (int) ($totals->total_out ?? 0),
            'balance' => (int) $customer->points,
        ];
    }
}
