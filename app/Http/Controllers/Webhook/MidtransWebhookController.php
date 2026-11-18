<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionTransaction;
use App\Models\Transaction;
use App\Services\Customer\ProductOrderService;
use App\Services\Customer\SubscriptionOrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly ProductOrderService $orderService,
        private readonly SubscriptionOrderService $subscriptionOrderService
    ) {}

    /**
     * Menerima notifikasi status transaksi HTTP POST dari Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Payload:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $signatureKey) {
            return response()->json(['message' => 'Parameter tidak lengkap.'], 400);
        }

        // Khusus pengujian konektivitas dari dashboard Midtrans (tombol "Test notification URL")
        if (str_starts_with((string) $orderId, 'payment_notif_test_')) {
            Log::info('Midtrans Webhook: Test notification ping diterima untuk '.$orderId);

            return response()->json([
                'status' => 'success',
                'message' => 'Test notification received successfully.',
            ], 200);
        }

        if (! $this->midtransService->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans Webhook: Invalid signature received for '.$orderId.'. Pastikan MIDTRANS_SERVER_KEY di .env sesuai dengan merchant Midtrans.');

            // Jika dalam mode produksi dan bukan di environment lokal, tolak request demi keamanan
            $isProduction = (bool) config('services.midtrans.is_production', false);
            if ($isProduction && ! app()->environment('local')) {
                return response()->json(['message' => 'Signature tidak valid.'], 403);
            }

            Log::info("Midtrans Webhook: Melanjutkan pembaruan status {$orderId} dalam mode Sandbox/Local testing.");
        }

        $parts = explode('-', $orderId);
        $prefix = $parts[0];
        $transactionId = $parts[1] ?? null;

        // Tangani Transaksi Langganan ReValue+ (SUB-xxx)
        if ($prefix === 'SUB') {
            $subTrx = SubscriptionTransaction::find($transactionId)
                ?? SubscriptionTransaction::where('midtrans_order_id', $orderId)->first();
            if (! $subTrx) {
                return response()->json(['message' => 'Transaksi langganan tidak ditemukan.'], 404);
            }

            if (empty($subTrx->midtrans_order_id)) {
                $subTrx->update(['midtrans_order_id' => $orderId]);
            }

            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                $this->subscriptionOrderService->settleSubscription($subTrx);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
                $customer = $subTrx->customer;
                if ($subTrx->status === TransactionStatus::PENDING && $customer instanceof Customer) {
                    $this->subscriptionOrderService->cancelSubscription($subTrx, $customer);
                }
            }

            return response()->json(['status' => 'success']);
        }

        // Tangani Transaksi Pembelian Produk (TRX-xxx)
        $transaction = Transaction::find($transactionId);
        if (! $transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        if (empty($transaction->midtrans_order_id)) {
            $transaction->update(['midtrans_order_id' => $orderId]);
        }

        // Tangani status dari Midtrans
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $transaction->update([
                    'status' => OrderStatus::PROCESSING,
                    'settled_at' => now(),
                ]);
            }
        } elseif ($transactionStatus === 'settlement') {
            $transaction->update([
                'status' => OrderStatus::PROCESSING,
                'settled_at' => now(),
            ]);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            if ($transaction->status === OrderStatus::PENDING) {
                $this->orderService->cancelOrder($transaction, $transaction->customer);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
