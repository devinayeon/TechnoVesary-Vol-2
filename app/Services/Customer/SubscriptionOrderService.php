<?php

namespace App\Services\Customer;

use App\Enums\PaymentMethod;
use App\Enums\PointType;
use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\PointHistory;
use App\Models\Subscription;
use App\Models\SubscriptionTransaction;
use App\Services\Payment\MidtransService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionOrderService
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Mengambil daftar paket langganan (1 Bulan, 3 Bulan, 1 Tahun) berdasarkan harga dasar subscription.
     *
     * @return array<string, array{
     *     key: string,
     *     name: string,
     *     duration_months: int,
     *     duration_days: int,
     *     multiplier: int,
     *     price: int,
     *     badge: string,
     *     badge_tone: string,
     *     description: string,
     *     expires_at_preview: string,
     *     is_recommended: bool
     * }>
     */
    public function getPackageDefinitions(Subscription $baseSub, ?Customer $customer = null): array
    {
        $basePrice = (int) $baseSub->price;
        if ($basePrice <= 0) {
            $basePrice = 30000;
        }

        $packages = [
            '1_month' => [
                'key' => '1_month',
                'name' => 'ReValue+ 1 Bulan',
                'duration_months' => 1,
                'duration_days' => 30,
                'multiplier' => 1,
                'price' => $basePrice * 1,
                'badge' => 'Layanan Penjemputan',
                'badge_tone' => 'Green',
                'description' => 'Penjemputan sampah terpilah rutin selama 30 hari penuh.',
                'is_recommended' => false,
            ],
            '3_months' => [
                'key' => '3_months',
                'name' => 'ReValue+ 3 Bulan',
                'duration_months' => 3,
                'duration_days' => 90,
                'multiplier' => 3,
                'price' => $basePrice * 3,
                'badge' => 'Paling Populer',
                'badge_tone' => 'Gold',
                'description' => 'Solusi hemat penjemputan teratur selama satu kuartal (3 bulan).',
                'is_recommended' => true,
            ],
            '1_year' => [
                'key' => '1_year',
                'name' => 'ReValue+ 1 Tahun',
                'duration_months' => 12,
                'duration_days' => 365,
                'multiplier' => 12,
                'price' => $basePrice * 12,
                'badge' => 'Terbaik & Terhemat',
                'badge_tone' => 'Blue',
                'description' => 'Investasi gaya hidup sirkular berkelanjutan untuk satu tahun penuh.',
                'is_recommended' => false,
            ],
        ];

        // Hitung estimasi tanggal berakhir setelah diperpanjang
        $currentExpiry = null;
        if ($customer && $customer->isActiveMember() && $customer->membership_expires_at) {
            $currentExpiry = $customer->membership_expires_at->copy();
        }

        $result = [];
        foreach ($packages as $key => $pkg) {
            $baseTime = $currentExpiry ? $currentExpiry->copy() : now();
            $expiresAt = $baseTime->addDays($pkg['duration_days'])->translatedFormat('d F Y');

            $result[$key] = array_merge($pkg, [
                'expires_at_preview' => $expiresAt,
            ]);
        }

        return $result;
    }

    /**
     * Menghitung rincian biaya dan diskon poin untuk paket yang dipilih.
     *
     * @return array{
     *     package: array<string, mixed>,
     *     total_amount: int,
     *     points_used: int,
     *     points_discount_amount: int,
     *     payable_amount: int,
     *     payment_method: PaymentMethod,
     *     is_full_points: bool
     * }
     */
    public function calculateCheckout(
        Subscription $baseSub,
        string $packageKey,
        Customer $customer,
        bool $usePoints
    ): array {
        $packages = $this->getPackageDefinitions($baseSub, $customer);
        if (! isset($packages[$packageKey])) {
            throw new InvalidArgumentException("Paket langganan '{$packageKey}' tidak valid.");
        }

        $package = $packages[$packageKey];
        $totalAmount = (int) $package['price'];
        $customerPoints = (int) $customer->points;

        $pointsUsed = 0;
        $pointsDiscount = 0;

        if ($usePoints && $customerPoints > 0) {
            if ($customerPoints >= $totalAmount) {
                // Bayar penuh menggunakan poin
                $pointsUsed = $totalAmount;
                $pointsDiscount = $totalAmount;
                $payableAmount = 0;
                $paymentMethod = PaymentMethod::POINTS;
                $isFullPoints = true;
            } else {
                // Poin memotong sebagian harga
                $pointsUsed = $customerPoints;
                $pointsDiscount = $customerPoints;
                $payableAmount = $totalAmount - $pointsDiscount;
                $paymentMethod = PaymentMethod::MIDTRANS;
                $isFullPoints = false;
            }
        } else {
            // Bayar penuh dengan uang via Midtrans
            $payableAmount = $totalAmount;
            $paymentMethod = PaymentMethod::MIDTRANS;
            $isFullPoints = false;
        }

        return [
            'package' => $package,
            'total_amount' => $totalAmount,
            'points_used' => $pointsUsed,
            'points_discount_amount' => $pointsDiscount,
            'payable_amount' => $payableAmount,
            'payment_method' => $paymentMethod,
            'is_full_points' => $isFullPoints,
        ];
    }

    /**
     * Membuat transaksi langganan ReValue+ baru.
     *
     * @return array{
     *     success: bool,
     *     transaction: SubscriptionTransaction,
     *     snap_token: ?string,
     *     snap_url: ?string,
     *     is_full_points: bool
     * }
     */
    public function createOrder(
        Customer $customer,
        string $packageKey,
        bool $usePoints = false
    ): array {
        $baseSub = Subscription::first();
        if (! $baseSub) {
            $baseSub = Subscription::create([
                'name' => 'ReValue+ Bulanan',
                'price' => 30000,
                'description' => 'Paket langganan dasar penjemputan sampah terpilah selama 30 hari.',
            ]);
        }

        $calc = $this->calculateCheckout($baseSub, $packageKey, $customer, $usePoints);

        return DB::transaction(function () use ($customer, $baseSub, $packageKey, $calc) {
            $isFullPoints = $calc['is_full_points'];
            $pointsUsed = $calc['points_used'];
            $package = $calc['package'];

            $status = $isFullPoints ? TransactionStatus::SETTLED : TransactionStatus::PENDING;
            $settledAt = $isFullPoints ? now() : null;

            // 1. Potong poin jika digunakan
            if ($pointsUsed > 0) {
                $customer->decrement('points', $pointsUsed);
                PointHistory::create([
                    'customer_id' => $customer->id,
                    'type' => PointType::OUT,
                    'amount' => $pointsUsed,
                    'source' => "Pembayaran Langganan ReValue+: {$package['name']}",
                ]);
            }

            // 2. Buat record SubscriptionTransaction
            $transaction = SubscriptionTransaction::create([
                'customer_id' => $customer->id,
                'subscription_id' => $baseSub->id,
                'package_key' => $packageKey,
                'duration_months' => $package['duration_months'],
                'total_amount' => $calc['total_amount'],
                'points_used' => $pointsUsed,
                'points_discount_amount' => $calc['points_discount_amount'],
                'payable_amount' => $calc['payable_amount'],
                'payment_method' => $calc['payment_method'],
                'status' => $status,
                'settled_at' => $settledAt,
            ]);

            // 3. Jika bayar penuh menggunakan poin -> aktifkan langganan seketika
            if ($isFullPoints) {
                $this->activateCustomerMembership($customer, (int) $package['duration_days']);

                return [
                    'success' => true,
                    'transaction' => $transaction,
                    'snap_token' => null,
                    'snap_url' => null,
                    'is_full_points' => true,
                ];
            }

            // 4. Jika butuh pembayaran via Midtrans -> generate Snap token
            $orderId = "SUB-{$transaction->id}-".time();
            $snapParams = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $calc['payable_amount'],
                ],
                'customer_details' => [
                    'first_name' => $customer->user->name ?? 'Customer ReValue',
                    'email' => $customer->user->email ?? 'customer@revalue.my.id',
                ],
                'item_details' => [
                    [
                        'id' => "SUB-{$packageKey}",
                        'price' => $calc['payable_amount'],
                        'quantity' => 1,
                        'name' => "{$package['name']} (Sisa Tagihan)",
                    ],
                ],
            ];

            $snapResult = $this->midtransService->createSnapTransaction($snapParams);

            $transaction->update([
                'midtrans_order_id' => $orderId,
                'snap_token' => $snapResult['token'] ?? null,
                'snap_url' => $snapResult['redirect_url'] ?? null,
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'snap_token' => $snapResult['token'] ?? null,
                'snap_url' => $snapResult['redirect_url'] ?? null,
                'is_full_points' => false,
            ];
        });
    }

    /**
     * Menyelesaikan transaksi langganan dan mengaktifkan keanggotaan customer (Idempoten).
     */
    public function settleSubscription(SubscriptionTransaction $transaction): void
    {
        if ($transaction->status === TransactionStatus::SETTLED) {
            return;
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => TransactionStatus::SETTLED,
                'settled_at' => now(),
            ]);

            $customer = $transaction->customer;
            if ($customer instanceof Customer) {
                $durationDays = $this->getDaysForPackage($transaction->package_key ?? '1_month');
                $this->activateCustomerMembership($customer, $durationDays);
            }
        });
    }

    /**
     * Membatalkan pesanan langganan pending dan mengembalikan poin jika ada.
     */
    public function cancelSubscription(SubscriptionTransaction $transaction, Customer $customer): void
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            throw new InvalidArgumentException('Hanya transaksi yang masih menunggu pembayaran yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($transaction, $customer) {
            // Kembalikan poin jika sebelumnya sempat terpotong
            if ($transaction->points_used > 0) {
                $customer->increment('points', $transaction->points_used);
                PointHistory::create([
                    'customer_id' => $customer->id,
                    'type' => PointType::IN,
                    'amount' => $transaction->points_used,
                    'source' => "Pengembalian Poin: Pembatalan Langganan #SUB-{$transaction->id}",
                ]);
            }

            $transaction->update([
                'status' => TransactionStatus::CANCELED,
                'canceled_at' => now(),
            ]);
        });
    }

    /**
     * Menghasilkan Snap token baru untuk melanjutkan pembayaran transaksi pending.
     *
     * @return array{token: string, redirect_url: string}
     */
    public function generateRepayToken(SubscriptionTransaction $transaction, Customer $customer): array
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            throw new InvalidArgumentException('Transaksi ini sudah tidak dalam status menunggu pembayaran.');
        }

        if (! empty($transaction->snap_token) && ! empty($transaction->snap_url)) {
            return [
                'token' => $transaction->snap_token,
                'redirect_url' => $transaction->snap_url,
            ];
        }

        $orderId = "SUB-{$transaction->id}-".time();
        $packageName = $this->getPackageName($transaction->package_key ?? '1_month');

        $snapParams = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $transaction->payable_amount,
            ],
            'customer_details' => [
                'first_name' => $customer->user->name ?? 'Customer ReValue',
                'email' => $customer->user->email ?? 'customer@revalue.my.id',
            ],
            'item_details' => [
                [
                    'id' => "SUB-{$transaction->package_key}",
                    'price' => $transaction->payable_amount,
                    'quantity' => 1,
                    'name' => "{$packageName} (Sisa Tagihan)",
                ],
            ],
        ];

        $snapResult = $this->midtransService->createSnapTransaction($snapParams);

        $transaction->update([
            'midtrans_order_id' => $orderId,
            'snap_token' => $snapResult['token'] ?? null,
            'snap_url' => $snapResult['redirect_url'] ?? null,
        ]);

        return [
            'token' => (string) $snapResult['token'],
            'redirect_url' => (string) $snapResult['redirect_url'],
        ];
    }

    /**
     * Memperpanjang masa aktif keanggotaan customer.
     */
    private function activateCustomerMembership(Customer $customer, int $durationDays): void
    {
        $baseTime = ($customer->isActiveMember() && $customer->membership_expires_at)
            ? $customer->membership_expires_at->copy()
            : now();

        $newExpires = $baseTime->addDays($durationDays);

        $customer->update([
            'is_member' => true,
            'membership_expires_at' => $newExpires,
        ]);
    }

    private function getDaysForPackage(string $key): int
    {
        return match ($key) {
            '3_months' => 90,
            '1_year' => 365,
            default => 30,
        };
    }

    private function getPackageName(string $key): string
    {
        return match ($key) {
            '3_months' => 'ReValue+ 3 Bulan',
            '1_year' => 'ReValue+ 1 Tahun',
            default => 'ReValue+ 1 Bulan',
        };
    }
}
