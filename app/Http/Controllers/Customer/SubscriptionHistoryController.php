<?php

namespace App\Http\Controllers\Customer;

use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Models\SubscriptionTransaction;
use App\Models\User;
use App\Services\Customer\SubscriptionOrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionHistoryController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly SubscriptionOrderService $subscriptionOrderService,
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Menampilkan riwayat transaksi langganan ReValue+ oleh customer.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);

        $filters = $request->only(['search', 'status']);

        $query = SubscriptionTransaction::where('customer_id', $customer->id)
            ->with('subscription');

        // Filter status transaksi
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter pencarian
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('midtrans_order_id', 'like', "%{$search}%")
                    ->orWhere('package_key', 'like', "%{$search}%")
                    ->orWhereHas('subscription', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $statuses = array_map(
            fn (TransactionStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            TransactionStatus::cases()
        );

        return Inertia::render('Customer/Subscription/History', [
            'transactions' => $transactions,
            'filters' => $filters,
            'statuses' => $statuses,
            'clientKey' => $this->midtransService->getClientKey(),
            'snapJsUrl' => $this->midtransService->getSnapJsUrl(),
            'customerPoints' => (int) $customer->points,
            'isCurrentMember' => $customer->isActiveMember(),
            'currentExpiresAt' => $customer->membership_expires_at?->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Memeriksa status pembayaran transaksi langganan secara langsung ke API Midtrans.
     */
    public function checkStatus(Request $request, SubscriptionTransaction $transaction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($transaction->status !== TransactionStatus::PENDING) {
            return back()->with('info', "Transaksi langganan #SUB-{$transaction->id} berstatus {$transaction->status->label()}.");
        }

        $orderId = $transaction->midtrans_order_id;
        if (empty($orderId)) {
            $orderId = "SUB-{$transaction->id}";
        }

        $result = $this->midtransService->getTransactionStatus($orderId);

        if (! $result['success']) {
            return back()->with('error', $result['message'] ?? 'Gagal memeriksa status ke Midtrans.');
        }

        $data = $result['data'] ?? [];
        $transactionStatus = $data['transaction_status'] ?? null;
        $fraudStatus = $data['fraud_status'] ?? null;

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $this->subscriptionOrderService->settleSubscription($transaction);

            return back()->with('success', "Pembayaran untuk Langganan #SUB-{$transaction->id} terverifikasi lunas! Keanggotaan ReValue+ Anda kini aktif.");
        }

        if ($transactionStatus === 'pending') {
            return back()->with('info', "Pembayaran untuk Langganan #SUB-{$transaction->id} masih menunggu penyelesaian di Midtrans.");
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $this->subscriptionOrderService->cancelSubscription($transaction, $customer);

            return back()->with('error', "Pembayaran untuk Langganan #SUB-{$transaction->id} telah {$transactionStatus}. Transaksi dibatalkan dan poin Anda dikembalikan.");
        }

        return back()->with('info', "Status transaksi di Midtrans: {$transactionStatus}.");
    }

    /**
     * Membatalkan transaksi langganan yang belum dibayar.
     */
    public function cancel(Request $request, SubscriptionTransaction $transaction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            abort(403, 'Akses tidak sah.');
        }

        try {
            $this->subscriptionOrderService->cancelSubscription($transaction, $customer);

            return back()->with('success', "Transaksi langganan #SUB-{$transaction->id} berhasil dibatalkan. Poin yang sempat dipotong telah dikembalikan ke saldo Anda.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menghasilkan token Snap Midtrans untuk melanjutkan pembayaran transaksi pending.
     */
    public function repay(Request $request, SubscriptionTransaction $transaction): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            return response()->json(['message' => 'Akses tidak sah.'], 403);
        }

        try {
            $snapResult = $this->subscriptionOrderService->generateRepayToken($transaction, $customer);

            return response()->json([
                'success' => true,
                'snap_token' => $snapResult['token'],
                'snap_url' => $snapResult['redirect_url'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
