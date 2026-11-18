<?php

namespace App\Services\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\PickupStatus;
use App\Models\Driver;
use App\Models\Transaction;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;

class DriverDashboardService
{
    /**
     * Mengambil ringkasan data fungsional dashboard untuk armada/driver.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(Driver $driver): array
    {
        $todayStart = now()->startOfDay();

        // 1. Tugas Penjemputan Sampah Aktif (Driver ditugaskan)
        $activePickupsQuery = WasteDeposit::where('driver_id', $driver->id)
            ->whereIn('status', [PickupStatus::ON_THE_WAY->value, PickupStatus::PICKED_UP->value])
            ->with(['customer.user', 'details.wasteCategory']);

        $activePickupsCount = $activePickupsQuery->count();

        // 2. Tugas Pengantaran Produk Aktif (Driver ditugaskan)
        $activeDeliveriesQuery = Transaction::where('driver_id', $driver->id)
            ->where('status', OrderStatus::DELIVERING->value)
            ->with(['customer.user', 'details.product']);

        $activeDeliveriesCount = $activeDeliveriesQuery->count();

        $totalActiveTasks = $activePickupsCount + $activeDeliveriesCount;

        // 3. Tugas Selesai Hari Ini
        $completedPickupsToday = WasteDeposit::where('driver_id', $driver->id)
            ->where('status', PickupStatus::COMPLETED->value)
            ->where('updated_at', '>=', $todayStart)
            ->count();

        $completedDeliveriesToday = Transaction::where('driver_id', $driver->id)
            ->whereIn('status', [OrderStatus::DELIVERED->value, OrderStatus::COMPLETED->value])
            ->where('updated_at', '>=', $todayStart)
            ->count();

        $totalCompletedToday = $completedPickupsToday + $completedDeliveriesToday;

        // 4. Total Akumulasi Muatan Sampah yang Pernah Diselesaikan Driver Ini
        $totalWeightCollected = (float) WasteDepositDetail::whereHas('wasteDeposit', function ($q) use ($driver) {
            $q->where('driver_id', $driver->id)
                ->where('status', PickupStatus::COMPLETED->value);
        })->sum('weight');

        $totalPickupsCompletedAllTime = WasteDeposit::where('driver_id', $driver->id)
            ->where('status', PickupStatus::COMPLETED->value)
            ->count();

        // 5. Total Akumulasi Pengantaran Produk Selesai
        $totalDeliveriesCompletedAllTime = Transaction::where('driver_id', $driver->id)
            ->whereIn('status', [OrderStatus::DELIVERED->value, OrderStatus::COMPLETED->value])
            ->count();

        // 6. Daftar Antrean Tugas Aktif & Tersedia (Penjemputan + Pengantaran)
        $tasks = [];

        // Masukkan jemputan aktif milik driver
        foreach ($activePickupsQuery->get() as $pickup) {
            $categories = $pickup->details
                ->map(fn ($d) => $d->wasteCategory?->name)
                ->filter()
                ->unique()
                ->values()
                ->join(', ');

            $weight = (float) $pickup->details->sum('weight');

            $tasks[] = [
                'id' => $pickup->id,
                'task_type' => 'pickup',
                'type_label' => 'Jemput Sampah',
                'name' => $pickup->customer?->user?->name ?? 'Nasabah ReValue',
                'address' => $pickup->address,
                'detail_info' => $weight > 0 ? "Est. {$weight} Kg ({$categories})" : ($categories ?: 'Sampah Terpilah'),
                'status' => $pickup->status->value,
                'status_label' => $pickup->status->shortLabel(),
                'status_tone' => $pickup->status->tone(),
                'action_url' => route('driver.pickups.show', $pickup),
                'action_label' => 'Buka Detail & Peta',
            ];
        }

        // Masukkan pengantaran aktif milik driver
        foreach ($activeDeliveriesQuery->get() as $delivery) {
            $itemCount = $delivery->details->sum('quantity');

            $tasks[] = [
                'id' => $delivery->id,
                'task_type' => 'delivery',
                'type_label' => 'Antar Produk',
                'name' => $delivery->customer?->user?->name ?? 'Pembeli Upcycle',
                'address' => $delivery->delivery_address,
                'detail_info' => "{$itemCount} Item Upcycle Store",
                'status' => $delivery->status->value,
                'status_label' => $delivery->status->label(),
                'status_tone' => 'Blue',
                'action_url' => route('driver.deliveries.show', $delivery),
                'action_label' => 'Buka Rute Pengantaran',
            ];
        }

        // Jika tugas aktif masih sedikit (< 3), tampilkan juga jemputan pending yang siap diambil (unassigned)
        if (count($tasks) < 5) {
            $pendingPickups = WasteDeposit::whereNull('driver_id')
                ->where('status', PickupStatus::PENDING->value)
                ->with(['customer.user', 'details.wasteCategory'])
                ->latest()
                ->take(5 - count($tasks))
                ->get();

            foreach ($pendingPickups as $pending) {
                $categories = $pending->details
                    ->map(fn ($d) => $d->wasteCategory?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->join(', ');

                $tasks[] = [
                    'id' => $pending->id,
                    'task_type' => 'pickup',
                    'type_label' => 'Jemput Sampah (Antrean Baru)',
                    'name' => $pending->customer?->user?->name ?? 'Nasabah ReValue',
                    'address' => $pending->address,
                    'detail_info' => $categories ?: 'Permintaan Jemputan Baru',
                    'status' => $pending->status->value,
                    'status_label' => 'Siap Diambil',
                    'status_tone' => 'Amber',
                    'action_url' => route('driver.pickups.show', $pending),
                    'action_label' => 'Ambil Tugas Ini',
                ];
            }
        }

        // 7. Perhitungan Progres Hari Ini
        $totalGoalToday = max(1, $totalActiveTasks + $totalCompletedToday);
        $progressPercentage = min(100, (int) round(($totalCompletedToday / $totalGoalToday) * 100));

        return [
            'metrics' => [
                'activeTasks' => [
                    'value' => "{$totalActiveTasks} Tugas",
                    'sublabel' => "{$totalCompletedToday} tugas selesai hari ini",
                ],
                'weightCollected' => [
                    'value' => number_format($totalWeightCollected, 1, ',', '.').' Kg',
                    'sublabel' => "Dari {$totalPickupsCompletedAllTime} penjemputan selesai",
                ],
                'deliveriesCompleted' => [
                    'value' => "{$totalDeliveriesCompletedAllTime} Terkirim",
                    'sublabel' => $activeDeliveriesCount > 0
                        ? "{$activeDeliveriesCount} paket dalam perjalanan"
                        : 'Semua paket telah terkirim',
                ],
            ],
            'tasks' => $tasks,
            'progress' => [
                'completed' => $totalCompletedToday,
                'total' => $totalGoalToday,
                'percentage' => $progressPercentage,
            ],
            'isAvailable' => (bool) $driver->is_available,
        ];
    }
}
