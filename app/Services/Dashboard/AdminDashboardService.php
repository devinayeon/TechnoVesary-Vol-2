<?php

namespace App\Services\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\PickupStatus;
use App\Enums\TransactionStatus;
use App\Models\Article;
use App\Models\SubscriptionTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Mengambil seluruh data dashboard admin yang telah teragregasi.
     */
    public function getDashboardData(): array
    {
        return [
            'metrics' => $this->getMetrics(),
            'chartBars' => $this->getMonthlyRevenueChart(6),
            'wasteComposition' => $this->getWasteComposition(),
            'recentPickups' => $this->getRecentPickups(5),
            'insight' => $this->getOperationalInsight(),
        ];
    }

    /**
     * 1. Empat Kartu Metrik Utama
     */
    public function getMetrics(): array
    {
        // Total Pengguna
        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

        // Total Penghasilan (Upcycle Store + Langganan ReValue+)
        $productRevenue = Transaction::where(function ($q) {
            $q->whereNotNull('settled_at')
                ->orWhereIn('status', [
                    OrderStatus::PROCESSING->value,
                    OrderStatus::DELIVERING->value,
                    OrderStatus::COMPLETED->value,
                ]);
        })->sum('payable_amount');

        $subscriptionRevenue = SubscriptionTransaction::where(function ($q) {
            $q->whereNotNull('settled_at')
                ->orWhere('status', TransactionStatus::SETTLED->value);
        })->sum('payable_amount');

        $totalRevenue = (int) ($productRevenue + $subscriptionRevenue);

        // Kategori Sampah
        $totalCategories = WasteCategory::count();

        // Total Artikel Edukasi
        $totalArticles = Article::count();

        return [
            'totalUsers' => [
                'value' => number_format($totalUsers, 0, ',', '.'),
                'sublabel' => $newUsersThisMonth > 0
                    ? "+{$newUsersThisMonth} pengguna baru bulan ini"
                    : 'Seluruh role aktif',
            ],
            'totalRevenue' => [
                'value' => $this->formatRupiahCompact($totalRevenue),
                'sublabel' => 'Penerimaan toko & langganan',
            ],
            'totalCategories' => [
                'value' => "{$totalCategories} Kategori",
                'sublabel' => 'Katalog sampah aktif',
            ],
            'totalArticles' => [
                'value' => "{$totalArticles} Materi",
                'sublabel' => 'Konten edukasi tersedia',
            ],
        ];
    }

    /**
     * 2. Bar Chart Penerimaan Rupiah 6 Bulan Terakhir
     */
    public function getMonthlyRevenueChart(int $months = 6): array
    {
        $startDate = now()->startOfMonth()->subMonths($months - 1);
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        // Buat daftar bulan dasar
        $monthsList = [];
        $current = $startDate->copy();
        for ($i = 0; $i < $months; $i++) {
            $key = $current->format('Y-m');
            $monthsList[$key] = [
                'year' => (int) $current->format('Y'),
                'month_num' => (int) $current->format('n'),
                'month' => $monthNames[(int) $current->format('n')],
                'raw_revenue' => 0,
                'isCurrent' => $current->isCurrentMonth(),
            ];
            $current = $current->addMonth();
        }

        // Ambil data Transaksi Produk per bulan
        $productRevenues = Transaction::select([
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
            DB::raw('SUM(payable_amount) as total'),
        ])
            ->where('created_at', '>=', $startDate)
            ->where(function ($q) {
                $q->whereNotNull('settled_at')
                    ->orWhereIn('status', [
                        OrderStatus::PROCESSING->value,
                        OrderStatus::DELIVERING->value,
                        OrderStatus::COMPLETED->value,
                    ]);
            })
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        // Ambil data Transaksi Langganan per bulan
        $subscriptionRevenues = SubscriptionTransaction::select([
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
            DB::raw('SUM(payable_amount) as total'),
        ])
            ->where('created_at', '>=', $startDate)
            ->where(function ($q) {
                $q->whereNotNull('settled_at')
                    ->orWhere('status', TransactionStatus::SETTLED->value);
            })
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        // Gabungkan nominal
        $maxRevenue = 0;
        foreach ($monthsList as $key => &$m) {
            $rev = ($productRevenues[$key] ?? 0) + ($subscriptionRevenues[$key] ?? 0);
            $m['raw_revenue'] = (int) $rev;
            if ($rev > $maxRevenue) {
                $maxRevenue = (int) $rev;
            }
        }
        unset($m);

        // Format chart bar untuk frontend
        $bars = [];
        $currentMonthHighlighted = false;
        foreach ($monthsList as $m) {
            // Kalkulasi persentase tinggi bar (minimum 8% untuk tampilan visual rapi)
            $heightPct = $maxRevenue > 0
                ? (int) round(($m['raw_revenue'] / $maxRevenue) * 90) + 10
                : 10;

            $isHighlighted = $m['isCurrent'];
            if ($isHighlighted) {
                $currentMonthHighlighted = true;
            }

            $bars[] = [
                'month' => $m['month'],
                'value' => $this->formatRupiahShort($m['raw_revenue']),
                'heightPercentage' => min($heightPct, 100),
                'isHighlighted' => $isHighlighted,
            ];
        }

        // Jika tidak ada yang saat ini, beri highlight pada bar terakhir
        if (! $currentMonthHighlighted && count($bars) > 0) {
            $bars[count($bars) - 1]['isHighlighted'] = true;
        }

        // Subtitle range bulan (misal: "Juni – November 2024")
        $firstMonthName = Carbon::parse($startDate)->locale('id')->isoFormat('MMMM');
        $lastMonthName = now()->locale('id')->isoFormat('MMMM Y');
        $dateRangeText = "{$firstMonthName} – {$lastMonthName}";

        return [
            'bars' => $bars,
            'dateRangeText' => $dateRangeText,
        ];
    }

    /**
     * 3. Komposisi Sampah per Kategori
     */
    public function getWasteComposition(): array
    {
        $colorPalette = [
            'bg-eco-green',
            'bg-eco-green/70',
            'bg-sky-blue/80',
            'bg-sunlight-gold',
            'bg-silver-tier/80',
            'bg-night-ink/60',
        ];

        // Agregasi berat per kategori sampah
        $categoryWeights = WasteDepositDetail::select([
            'waste_category_id',
            DB::raw('SUM(weight) as total_weight'),
        ])
            ->groupBy('waste_category_id')
            ->with('wasteCategory:id,name')
            ->get();

        $totalWeightAll = $categoryWeights->sum('total_weight');

        // Jika belum ada setoran sama sekali, ambil semua kategori sampah sebagai 0 Kg
        if ($categoryWeights->isEmpty()) {
            $categories = WasteCategory::take(5)->get();
            $result = [];
            foreach ($categories as $index => $cat) {
                $result[] = [
                    'label' => $cat->name,
                    'value' => '0 Kg',
                    'percentage' => 0,
                    'color' => $colorPalette[$index % count($colorPalette)],
                ];
            }

            return $result;
        }

        // Sort descending by weight
        $sorted = $categoryWeights->sortByDesc('total_weight')->values();

        $result = [];
        foreach ($sorted as $index => $item) {
            $weight = (float) $item->total_weight;
            $percentage = $totalWeightAll > 0
                ? (int) round(($weight / $totalWeightAll) * 100)
                : 0;

            $result[] = [
                'label' => $item->wasteCategory?->name ?? 'Kategori Lain',
                'value' => $this->formatWeight($weight),
                'percentage' => min($percentage, 100),
                'color' => $colorPalette[$index % count($colorPalette)],
            ];
        }

        return $result;
    }

    /**
     * 4. Lima Penjemputan Sampah Terbaru
     */
    public function getRecentPickups(int $limit = 5): array
    {
        $pickups = WasteDeposit::with([
            'customer.user:id,name,email,photo',
            'details',
        ])
            ->latest('created_at')
            ->take($limit)
            ->get();

        return $pickups->map(function (WasteDeposit $p) {
            $totalWeight = $p->details->sum('weight');
            $status = $p->status;

            // Tone mapping
            $statusTone = match ($status) {
                PickupStatus::COMPLETED => 'Green',
                PickupStatus::ON_THE_WAY, PickupStatus::PICKED_UP => 'Blue',
                PickupStatus::PENDING => 'Amber',
                default => 'Neutral',
            };

            $statusText = match ($status) {
                PickupStatus::COMPLETED => 'Selesai',
                PickupStatus::PICKED_UP => 'Ditimbang',
                PickupStatus::ON_THE_WAY => 'Dijemput',
                PickupStatus::PENDING => 'Menunggu',
                PickupStatus::CANCELED => 'Batal',
                default => $status->value ?? 'Status',
            };

            $dateFormatted = $p->created_at
                ? $p->created_at->locale('id')->isoFormat('D MMM Y')
                : '-';

            return [
                'name' => $p->customer?->user?->name ?? 'Customer ReValue',
                'meta' => "#JMP-{$p->id} · {$dateFormatted}",
                'weight' => number_format((float) $totalWeight, 1, ',', '.').' Kg',
                'statusText' => $statusText,
                'statusTone' => $statusTone,
            ];
        })->toArray();
    }

    /**
     * 5. Insight Operasional Mingguan
     */
    public function getOperationalInsight(): array
    {
        $sevenDaysAgo = now()->subDays(7);
        $completedCount = WasteDeposit::where('status', PickupStatus::COMPLETED->value)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();

        $message = "{$completedCount} penjemputan berhasil diselesaikan 7 hari terakhir. Terus pantau kategori sampah dan ajak customer baru bergabung.";

        return [
            'completedThisWeek' => $completedCount,
            'message' => $message,
        ];
    }

    // --- Format Helper Methods ---

    private function formatRupiahCompact(int $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp '.number_format($amount / 1_000_000_000, 2, ',', '.').' M';
        }
        if ($amount >= 1_000_000) {
            return 'Rp '.number_format($amount / 1_000_000, 2, ',', '.').' Jt';
        }
        if ($amount >= 1_000) {
            return 'Rp '.number_format($amount / 1_000, 0, ',', '.').' Rb';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function formatRupiahShort(int $amount): string
    {
        if ($amount <= 0) {
            return '0';
        }
        if ($amount >= 1_000_000) {
            $formatted = number_format($amount / 1_000_000, $amount % 1_000_000 === 0 ? 0 : 1, ',', '.');

            return "{$formatted}jt";
        }
        if ($amount >= 1_000) {
            $formatted = number_format($amount / 1_000, 0, ',', '.');

            return "{$formatted}rb";
        }

        return (string) $amount;
    }

    private function formatWeight(float $weight): string
    {
        if ($weight >= 1000) {
            return number_format($weight / 1000, 1, ',', '.').' Ton';
        }

        return number_format($weight, 1, ',', '.').' Kg';
    }
}
