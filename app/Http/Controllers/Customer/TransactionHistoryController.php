<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerProfile;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Customer\ProductOrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionHistoryController extends Controller
{
    use ResolvesCustomerProfile;

    public function __construct(
        private readonly ProductOrderService $orderService,
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Menampilkan riwayat transaksi pembelian produk oleh customer.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveCustomer($request);

        $filters = $request->only(['search', 'status']);

        $query = Transaction::where('customer_id', $customer->id)
            ->with([
                'details.product',
                'driver' => function ($q) {
                    $q->select('id', 'user_id', 'current_latitude', 'current_longitude', 'location_updated_at')
                        ->with('user:id,name,email,photo');
                },
            ]);

        // Filter status pesanan
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter pencarian berdasarkan nama produk atau ID transaksi
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%")
                    ->orWhereHas('details.product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $statuses = array_map(
            fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            OrderStatus::cases()
        );

        return Inertia::render('Customer/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'statuses' => $statuses,
            'clientKey' => $this->midtransService->getClientKey(),
            'snapJsUrl' => $this->midtransService->getSnapJsUrl(),
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Membatalkan pesanan yang belum dibayar (status pending).
     */
    public function cancel(Request $request, Transaction $transaction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            abort(403, 'Akses tidak sah.');
        }

        try {
            $this->orderService->cancelOrder($transaction, $customer);

            return back()->with('success', "Pesanan #{$transaction->id} berhasil dibatalkan. Stok produk dan poin Anda telah dikembalikan.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Memeriksa status pembayaran transaksi secara langsung ke API Midtrans.
     */
    public function checkStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($transaction->status !== OrderStatus::PENDING) {
            return back()->with('info', "Pesanan #{$transaction->id} berstatus {$transaction->status->label()}.");
        }

        if ($transaction->payment_method->value !== 'midtrans') {
            return back()->with('info', 'Transaksi ini tidak menggunakan pembayaran Midtrans.');
        }

        $orderId = $transaction->midtrans_order_id;
        if (empty($orderId)) {
            // Coba order id format TRX-{id} jika belum tersimpan
            $orderId = 'TRX-'.$transaction->id;
        }

        $result = $this->midtransService->getTransactionStatus($orderId);

        if (! $result['success']) {
            return back()->with('error', $result['message'] ?? 'Gagal memeriksa status ke Midtrans.');
        }

        $data = $result['data'] ?? [];
        $transactionStatus = $data['transaction_status'] ?? null;
        $fraudStatus = $data['fraud_status'] ?? null;

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $transaction->update([
                'status' => OrderStatus::PROCESSING,
                'settled_at' => now(),
            ]);

            return back()->with('success', "Pembayaran untuk Pesanan #{$transaction->id} terverifikasi lunas! Pesanan Anda kini sedang diproses gudang.");
        }

        if ($transactionStatus === 'pending') {
            return back()->with('info', "Pembayaran untuk Pesanan #{$transaction->id} belum terdeteksi di Midtrans. Silakan selesaikan pembayaran terlebih dahulu.");
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $this->orderService->cancelOrder($transaction, $customer);

            return back()->with('error', "Pembayaran untuk Pesanan #{$transaction->id} telah {$transactionStatus}. Pesanan dibatalkan otomatis dan stok dikembalikan.");
        }

        return back()->with('info', "Status transaksi di Midtrans: {$transactionStatus}.");
    }

    /**
     * Mengonfirmasi penerimaan barang oleh customer untuk menyelesaikan transaksi.
     */
    public function confirmReceived(Request $request, Transaction $transaction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($transaction->status !== OrderStatus::DELIVERED) {
            return back()->with('error', 'Hanya pesanan yang sudah berstatus Terkirim yang dapat diselesaikan.');
        }

        $transaction->update([
            'status' => OrderStatus::COMPLETED,
        ]);

        return back()->with('success', "Pesanan #TRX-{$transaction->id} telah berhasil diselesaikan! Terima kasih telah berbelanja produk daur ulang di ReValue.");
    }

    /**
     * Mengambil token Midtrans Snap untuk melanjutkan pembayaran pesanan pending.
     */
    public function repay(Request $request, Transaction $transaction): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $customer = $user->customer;

        if (! $customer || $transaction->customer_id !== $customer->id) {
            return response()->json(['message' => 'Akses tidak sah.'], 403);
        }

        try {
            $snapResult = $this->orderService->generateRepayToken($transaction, $customer);

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
