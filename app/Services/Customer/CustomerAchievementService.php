<?php

namespace App\Services\Customer;

use App\Enums\OrderStatus;
use App\Enums\PickupStatus;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\WasteDepositDetail;

class CustomerAchievementService
{
    /**
     * Mengambil struktur lengkap data pencapaian (achievements) untuk Customer.
     *
     * @return array{
     *     summary: array{
     *         total_unlocked: int,
     *         total_badges: int,
     *         unlocked_text: string,
     *         unlocked_subtext: string,
     *         completed_pickups: int,
     *         next_pickup_target_text: string,
     *         total_weight_kg: string,
     *         total_weight_raw: float,
     *         next_weight_target_text: string
     *     },
     *     earned_badges: array<int, array<string, mixed>>,
     *     in_progress_badges: array<int, array<string, mixed>>,
     *     all_badges: array<int, array<string, mixed>>
     * }
     */
    public function getAchievementsData(Customer $customer): array
    {
        // 1. Data Riil Aktivitas Customer
        $completedPickups = $customer->wasteDeposits()
            ->where('status', PickupStatus::COMPLETED->value)
            ->count();

        $totalWeight = (float) WasteDepositDetail::whereHas('wasteDeposit', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->where('status', PickupStatus::COMPLETED->value);
        })->sum('weight');

        $completedTransactions = Transaction::where('customer_id', $customer->id)
            ->where(function ($q) {
                $q->whereIn('status', [OrderStatus::COMPLETED->value, OrderStatus::DELIVERED->value])
                    ->orWhereNotNull('settled_at');
            })
            ->count();

        // 2. Daftar 6 Milestone Badges
        $badgeDefinitions = [
            [
                'id' => 'first_pickup',
                'title' => 'Langkah Hijau Pertama',
                'description' => 'Selesaikan 1 kali penjemputan sampah terpilah',
                'detailed_note' => 'Lakukan 1 kali penjemputan sampah terpilah untuk membuka lencana ini. Pesanan dibatalkan tidak menambah progres.',
                'category' => 'pickup',
                'target' => 1,
                'current' => min($completedPickups, 1),
                'reward_points' => 500,
                'milestone_pill' => '1',
                'pill_color' => 'coral',
                'badge_image' => '/assets/shield/Shield 2.png',
            ],
            [
                'id' => 'routine_saver',
                'title' => 'Penabung Sampah Rutin',
                'description' => 'Selesaikan 5 kali penjemputan sampah terpilah',
                'detailed_note' => 'Kumpulkan sampah secara konsisten hingga mencapai 5 kali penjemputan selesai.',
                'category' => 'pickup',
                'target' => 5,
                'current' => min($completedPickups, 5),
                'reward_points' => 2000,
                'milestone_pill' => '5',
                'pill_color' => 'green',
                'badge_image' => '/assets/shield/Shield 1.png',
            ],
            [
                'id' => 'eco_warrior',
                'title' => 'Pejuang Lingkungan',
                'description' => 'Selesaikan 10 kali penjemputan sampah terpilah',
                'detailed_note' => 'Buktikan dedikasimu menjaga lingkungan dengan menyelesaikan 10 kali penjemputan sampah.',
                'category' => 'pickup',
                'target' => 10,
                'current' => min($completedPickups, 10),
                'reward_points' => 5000,
                'milestone_pill' => '10',
                'pill_color' => 'green',
                'badge_image' => '/assets/shield/Shield 4.png',
            ],
            [
                'id' => 'waste_hero',
                'title' => 'Pahlawan Bank Sampah',
                'description' => 'Selesaikan 25 kali penjemputan sampah terpilah',
                'detailed_note' => 'Selesaikan 25 penjemputan sampah. Pesanan dibatalkan tidak menambah progres.',
                'category' => 'pickup',
                'target' => 25,
                'current' => min($completedPickups, 25),
                'reward_points' => 15000,
                'milestone_pill' => '25',
                'pill_color' => 'gold',
                'badge_image' => '/assets/shield/Shield 6.png',
            ],
            [
                'id' => 'recycle_collector',
                'title' => 'Kolektor Daur Ulang',
                'description' => 'Kumpulkan total minimal 50 kg sampah terpilah',
                'detailed_note' => 'Kumpulkan dan setor sampah terpilah hingga mencapai total 50 kg.',
                'category' => 'weight',
                'target' => 50,
                'current' => min((int) round($totalWeight), 50),
                'reward_points' => 10000,
                'milestone_pill' => '50',
                'pill_color' => 'charcoal',
                'badge_image' => '/assets/shield/Shield 5.png',
            ],
            [
                'id' => 'circular_pioneer',
                'title' => 'Pelopor Ekonomi Sirkular',
                'description' => 'Lakukan 3 transaksi belanja produk daur ulang / penukaran poin',
                'detailed_note' => 'Tukarkan poinmu atau beli produk hasil daur ulang di Upcycle Store minimal 3 kali.',
                'category' => 'transaction',
                'target' => 3,
                'current' => min($completedTransactions, 3),
                'reward_points' => 3000,
                'milestone_pill' => '3',
                'pill_color' => 'purple',
                'badge_image' => '/assets/shield/Shield 3.png',
            ],
        ];

        $allBadges = [];
        $earnedBadges = [];
        $inProgressBadges = [];

        foreach ($badgeDefinitions as $badge) {
            $current = $badge['current'];
            $target = $badge['target'];
            $isUnlocked = $current >= $target;
            $percentage = (int) min(100, round(($current / $target) * 100));
            $formattedReward = number_format($badge['reward_points'], 0, ',', '.');

            $progressText = match ($badge['category']) {
                'weight' => "{$current} / {$target} kg",
                'transaction' => "{$current} / {$target} transaksi poin",
                default => "{$current} / {$target} selesai",
            };

            $rewardText = $isUnlocked
                ? "+{$formattedReward} poin diterima"
                : "Bonus +{$formattedReward} poin";

            $badgeData = array_merge($badge, [
                'is_unlocked' => $isUnlocked,
                'progress_percentage' => $percentage,
                'progress_text' => $progressText,
                'reward_text' => $rewardText,
                'locked_badge_image' => '/assets/shield/Shield.png',
            ]);

            $allBadges[] = $badgeData;
            if ($isUnlocked) {
                $earnedBadges[] = $badgeData;
            } else {
                $inProgressBadges[] = $badgeData;
            }
        }

        $totalUnlocked = count($earnedBadges);
        $totalBadges = count($badgeDefinitions);

        $nextWeightText = $totalWeight >= 50
            ? 'Target 50 kg tercapai!'
            : (int) ceil(50 - $totalWeight).' kg lagi menuju 50 kg';

        $nextPickupText = $completedPickups >= 25
            ? 'Target 25 penjemputan tercapai!'
            : 'Menuju target 25 kali';

        return [
            'summary' => [
                'total_unlocked' => $totalUnlocked,
                'total_badges' => $totalBadges,
                'unlocked_text' => "{$totalUnlocked} dari {$totalBadges}",
                'unlocked_subtext' => 'Bonus diberikan satu kali',
                'completed_pickups' => $completedPickups,
                'next_pickup_target_text' => $nextPickupText,
                'total_weight_kg' => number_format($totalWeight, 2, ',', '.').' kg',
                'total_weight_raw' => $totalWeight,
                'next_weight_target_text' => $nextWeightText,
            ],
            'earned_badges' => $earnedBadges,
            'in_progress_badges' => $inProgressBadges,
            'all_badges' => $allBadges,
        ];
    }
}
