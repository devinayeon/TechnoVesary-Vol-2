<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Models\Subscription;
use App\Services\Customer\SubscriptionOrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPlanController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly SubscriptionOrderService $subscriptionOrderService,
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Menampilkan katalog pilihan paket langganan ReValue+ dan status keanggotaan saat ini.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);

        $baseSub = Subscription::first();
        if (! $baseSub) {
            $baseSub = Subscription::create([
                'name' => 'ReValue+ Bulanan',
                'price' => 30000,
                'description' => 'Paket langganan dasar penjemputan sampah terpilah selama 30 hari.',
            ]);
        }

        $packages = $this->subscriptionOrderService->getPackageDefinitions($baseSub, $customer);

        $isMember = $customer->isActiveMember();
        $membershipExpiresAt = $customer->membership_expires_at
            ? $customer->membership_expires_at->translatedFormat('d F Y')
            : null;

        return Inertia::render('Customer/Subscription/Index', [
            'baseSubscription' => [
                'id' => $baseSub->id,
                'name' => $baseSub->name,
                'price' => (int) $baseSub->price,
                'description' => $baseSub->description,
            ],
            'packages' => array_values($packages),
            'membership' => [
                'is_active' => $isMember,
                'expires_at_formatted' => $membershipExpiresAt,
                'expires_at_raw' => $customer->membership_expires_at?->toIso8601String(),
            ],
            'customerPoints' => (int) $customer->points,
        ]);
    }

    /**
     * Menampilkan halaman checkout paket langganan ReValue+.
     */
    public function checkout(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);

        $packageKey = $request->query('package', '1_month');
        if (! in_array($packageKey, ['1_month', '3_months', '1_year'], true)) {
            $packageKey = '1_month';
        }

        $baseSub = Subscription::first();
        if (! $baseSub) {
            $baseSub = Subscription::create([
                'name' => 'ReValue+ Bulanan',
                'price' => 30000,
                'description' => 'Paket langganan dasar penjemputan sampah terpilah selama 30 hari.',
            ]);
        }

        $packages = $this->subscriptionOrderService->getPackageDefinitions($baseSub, $customer);
        $selectedPackage = $packages[$packageKey] ?? $packages['1_month'];

        return Inertia::render('Customer/Subscription/Checkout', [
            'package' => $selectedPackage,
            'customerPoints' => (int) $customer->points,
            'clientKey' => $this->midtransService->getClientKey(),
            'snapJsUrl' => $this->midtransService->getSnapJsUrl(),
            'isCurrentMember' => $customer->isActiveMember(),
            'currentExpiresAt' => $customer->membership_expires_at?->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Memproses pesanan langganan baru (Poin penuh atau Midtrans).
     */
    public function processCheckout(Request $request): JsonResponse
    {
        $request->validate([
            'package_key' => ['required', 'string', 'in:1_month,3_months,1_year'],
            'use_points' => ['nullable', 'boolean'],
        ]);

        $customer = $this->resolveCustomer($request);
        $packageKey = $request->input('package_key');
        $usePoints = (bool) $request->input('use_points', false);

        try {
            $result = $this->subscriptionOrderService->createOrder($customer, $packageKey, $usePoints);

            return response()->json([
                'success' => true,
                'message' => $result['is_full_points']
                    ? 'Langganan ReValue+ berhasil diaktifkan dengan Poin Reward!'
                    : 'Pesanan langganan berhasil dibuat. Silakan selesaikan pembayaran.',
                'transaction_id' => $result['transaction']->id,
                'snap_token' => $result['snap_token'],
                'snap_url' => $result['snap_url'],
                'is_full_points' => $result['is_full_points'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Gagal memproses langganan.',
            ], 422);
        }
    }
}
