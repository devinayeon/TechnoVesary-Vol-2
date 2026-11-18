<?php

namespace App\Services\Dashboard;

use App\Enums\PickupStatus;
use App\Models\Customer;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;

class CustomerDashboardService
{
    /**
     * Mengambil ringkasan data fungsional dashboard untuk nasabah (Customer).
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(Customer $customer): array
    {
        // 1. Saldo Poin & Poin Bulan Ini
        $currentPoints = (int) $customer->points;
        $pointsThisMonth = (int) $customer->pointHistories()
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('type', 'in')
            ->sum('amount');

        // 2. Total Sampah Disetor (Kg) dari penjemputan yang completed
        $totalWeight = (float) WasteDepositDetail::whereHas('wasteDeposit', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->where('status', PickupStatus::COMPLETED->value);
        })->sum('weight');

        $weightThisMonth = (float) WasteDepositDetail::whereHas('wasteDeposit', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->where('status', PickupStatus::COMPLETED->value)
                ->where('created_at', '>=', now()->startOfMonth());
        })->sum('weight');

        $completedPickupsCount = $customer->wasteDeposits()
            ->where('status', PickupStatus::COMPLETED->value)
            ->count();

        // 3. Status Keanggotaan ReValue+
        $isMember = $customer->isActiveMember();
        $membershipExpiresAt = $customer->membership_expires_at?->translatedFormat('d F Y');

        // 4. Riwayat Setoran Terakhir (3 setoran terbaru)
        $recentDeposits = $customer->wasteDeposits()
            ->with(['details.wasteCategory', 'driver.user'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function (WasteDeposit $dep) {
                $categories = $dep->details
                    ->map(fn ($d) => $d->wasteCategory?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->join(', ');

                $points = (int) $dep->details->sum('subtotal_points');
                $weight = (float) $dep->details->sum('weight');

                return [
                    'id' => $dep->id,
                    'date' => ($dep->pickup_at ?? $dep->created_at)?->translatedFormat('d M Y') ?? '-',
                    'weight' => $weight > 0 ? "{$weight} Kg" : 'Menunggu Timbang',
                    'earning' => $points > 0 ? "+{$points} Poin" : $dep->status->shortLabel(),
                    'types' => ! empty($categories) ? $categories : 'Sampah Terpilah',
                    'status' => $dep->status->value,
                    'status_label' => $dep->status->shortLabel(),
                    'status_tone' => $dep->status->tone(),
                    'show_url' => route('customer.pickups.show', $dep),
                ];
            })
            ->toArray();

        // 5. Pencapaian / Badges berdasarkan data riil customer
        $badges = [
            [
                'name' => 'Pilah Pemula',
                'desc' => 'Menyetor sampah pertama kali',
                'icon' => '🌱',
                'unlocked' => $completedPickupsCount >= 1,
            ],
            [
                'name' => 'Pilah Master',
                'desc' => 'Menyetor total minimal 10 Kg',
                'icon' => '🏆',
                'unlocked' => $totalWeight >= 10,
            ],
            [
                'name' => 'Eco Saver',
                'desc' => 'Kumpulkan 5.000 Poin reward',
                'icon' => '🌟',
                'unlocked' => $currentPoints >= 5000,
            ],
            [
                'name' => 'ReValue+ Member',
                'desc' => 'Keanggotaan prioritas aktif',
                'icon' => '💎',
                'unlocked' => $isMember,
            ],
        ];

        return [
            'metrics' => [
                'points' => [
                    'value' => number_format($currentPoints, 0, ',', '.').' Poin',
                    'sublabel' => $pointsThisMonth > 0
                        ? "+{$pointsThisMonth} poin bulan ini"
                        : 'Setara Rp '.number_format($currentPoints, 0, ',', '.'),
                ],
                'waste' => [
                    'value' => number_format($totalWeight, 1, ',', '.').' Kg',
                    'sublabel' => $weightThisMonth > 0
                        ? "+{$weightThisMonth} Kg bulan ini"
                        : "Dari {$completedPickupsCount} kali penjemputan",
                ],
                'membership' => [
                    'is_member' => $isMember,
                    'value' => $isMember ? 'ReValue+ Aktif' : 'Reguler',
                    'sublabel' => $isMember ? "Berlaku s.d {$membershipExpiresAt}" : 'Tingkatkan untuk jemput instan',
                ],
            ],
            'recentDeposits' => $recentDeposits,
            'badges' => $badges,
            'totalWeightKg' => $totalWeight,
            'isMember' => $isMember,
        ];
    }
}
