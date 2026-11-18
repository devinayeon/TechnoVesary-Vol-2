<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionManagementService
{
    /**
     * Mendapatkan paket langganan aktif utama.
     */
    public function getActiveSubscription(): Subscription
    {
        $subscription = Subscription::first();

        if (! $subscription) {
            $subscription = Subscription::create([
                'name' => 'ReValue+ Bulanan',
                'price' => 25000,
                'description' => 'Penjemputan sampah terpilah dari rumah, penimbangan transparan, dan poin dari hasil setoran.',
            ]);
        }

        return $subscription;
    }

    /**
     * Memperbarui data harga dan deskripsi paket langganan.
     *
     * @param array<string, mixed> $data
     */
    public function updatePrice(Subscription $subscription, array $data): Subscription
    {
        $subscription->update([
            'price' => (int) $data['price'],
            'description' => $data['description'] ?? $subscription->description,
        ]);

        return $subscription->fresh();
    }

    /**
     * Mengambil daftar transaksi subscription terpaginasi dengan pencarian dan filter status.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedTransactions(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SubscriptionTransaction::query()
            ->with(['customer.user', 'subscription']);

        // Filter Status
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter Search (ID transaksi, nama customer, email customer)
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $searchClean = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $searchClean) {
                if (! empty($searchClean)) {
                    $q->where('id', (int) $searchClean);
                }

                $q->orWhereHas('customer.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            });
        }

        return $query->latest('created_at')->paginate($perPage)->withQueryString();
    }
}
