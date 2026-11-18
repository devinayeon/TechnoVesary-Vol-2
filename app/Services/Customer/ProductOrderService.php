<?php

namespace App\Services\Customer;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PointType;
use App\Models\Customer;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Payment\MidtransService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductOrderService
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Membuat pesanan produk baru dengan dukungan pemotongan poin ReValue dan/atau Midtrans Gateway.
     */
    public function createOrder(
        Customer $customer,
        Product $product,
        int $quantity,
        string $deliveryAddress,
        ?float $latitude = null,
        ?float $longitude = null,
        bool $usePoints = false
    ): array {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Jumlah produk yang dibeli minimal 1 item.');
        }

        if ($product->stock < $quantity) {
            throw new InvalidArgumentException("Stok produk tidak mencukupi (Tersisa {$product->stock} item).");
        }

        $totalAmount = (int) ($product->price_rupiah * $quantity);
        $customerPoints = (int) $customer->points;

        $pointsUsed = 0;
        $pointsDiscount = 0;

        if ($usePoints && $customerPoints > 0) {
            if ($customerPoints >= $totalAmount) {
                // Bayar penuh 100% menggunakan Poin
                $pointsUsed = $totalAmount;
                $pointsDiscount = $totalAmount;
                $payableAmount = 0;
                $paymentMethod = PaymentMethod::POINTS;
                $status = OrderStatus::PROCESSING;
                $settledAt = now();
            } else {
                // Poin memotong sebagian harga, sisa dibayar via Midtrans
                $pointsUsed = $customerPoints;
                $pointsDiscount = $customerPoints;
                $payableAmount = $totalAmount - $pointsDiscount;
                $paymentMethod = PaymentMethod::MIDTRANS;
                $status = OrderStatus::PENDING;
                $settledAt = null;
            }
        } else {
            // Bayar penuh menggunakan Midtrans
            $payableAmount = $totalAmount;
            $paymentMethod = PaymentMethod::MIDTRANS;
            $status = OrderStatus::PENDING;
            $settledAt = null;
        }

        return DB::transaction(function () use (
            $customer,
            $product,
            $quantity,
            $deliveryAddress,
            $latitude,
            $longitude,
            $totalAmount,
            $pointsUsed,
            $pointsDiscount,
            $payableAmount,
            $paymentMethod,
            $status,
            $settledAt
        ) {
            // 1. Kurangi stok produk
            $product->decrement('stock', $quantity);

            // 2. Kurangi poin customer jika digunakan
            if ($pointsUsed > 0) {
                $customer->decrement('points', $pointsUsed);
                PointHistory::create([
                    'customer_id' => $customer->id,
                    'type' => PointType::OUT,
                    'amount' => $pointsUsed,
                    'source' => "Belanja Upcycle: {$product->name} ({$quantity} item)",
                ]);
            }

            // 3. Simpan data transaksi induk
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'driver_id' => null,
                'total_amount' => $totalAmount,
                'points_used' => $pointsUsed,
                'points_discount_amount' => $pointsDiscount,
                'payable_amount' => $payableAmount,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'delivery_address' => $deliveryAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'settled_at' => $settledAt,
            ]);

            // 4. Simpan rincian detail produk yang dibeli
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price_rupiah,
            ]);

            $snapToken = null;
            $snapUrl = null;

            // 5. Generate Midtrans Snap jika ada sisa nominal rupiah
            if ($payableAmount > 0) {
                $itemDetails = [
                    [
                        'id' => 'PROD-' . $product->id,
                        'price' => $product->price_rupiah,
                        'quantity' => $quantity,
                        'name' => substr($product->name, 0, 50),
                    ],
                ];

                if ($pointsDiscount > 0) {
                    $itemDetails[] = [
                        'id' => 'DISC-POINTS',
                        'price' => -$pointsDiscount,
                        'quantity' => 1,
                        'name' => 'Potongan Poin ReValue',
                    ];
                }

                $orderId = 'TRX-' . $transaction->id . '-' . time();
                $params = [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => $payableAmount,
                    ],
                    'customer_details' => [
                        'first_name' => $customer->user?->name ?? 'Customer ReValue',
                        'email' => $customer->user?->email ?? 'customer@revalue.id',
                    ],
                    'item_details' => $itemDetails,
                ];

                $snapResult = $this->midtransService->createSnapTransaction($params);
                $snapToken = $snapResult['token'];
                $snapUrl = $snapResult['redirect_url'];

                $transaction->update([
                    'snap_url' => $snapUrl,
                    'midtrans_order_id' => $orderId,
                ]);
            }

            return [
                'transaction' => $transaction->load(['details.product']),
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
                'is_paid_fully' => $payableAmount === 0,
            ];
        });
    }

    /**
     * Membatalkan pesanan yang masih pending, me-restore stok dan me-refund poin.
     */
    public function cancelOrder(Transaction $transaction, Customer $customer): void
    {
        if ($transaction->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Anda tidak memiliki wewenang membatalkan transaksi ini.');
        }

        if ($transaction->status !== OrderStatus::PENDING) {
            throw new InvalidArgumentException('Hanya transaksi menunggu pembayaran yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($transaction, $customer) {
            $transaction->update([
                'status' => OrderStatus::CANCELED,
                'canceled_at' => now(),
            ]);

            // Restore stok produk
            foreach ($transaction->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                }
            }

            // Refund poin customer jika ada
            if ($transaction->points_used > 0) {
                $customer->increment('points', $transaction->points_used);
                PointHistory::create([
                    'customer_id' => $customer->id,
                    'type' => PointType::IN,
                    'amount' => $transaction->points_used,
                    'source' => "Pengembalian Poin Pembatalan Pesanan #{$transaction->id}",
                ]);
            }
        });
    }

    /**
     * Menghasilkan Snap Token baru untuk melanjutkan pembayaran transaksi pending.
     */
    public function generateRepayToken(Transaction $transaction, Customer $customer): array
    {
        if ($transaction->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Anda tidak memiliki wewenang untuk pesanan ini.');
        }

        if ($transaction->status !== OrderStatus::PENDING || $transaction->payable_amount <= 0) {
            throw new InvalidArgumentException('Pesanan ini tidak memerlukan pembayaran lagi.');
        }

        $orderId = 'TRX-' . $transaction->id . '-' . time();
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $transaction->payable_amount,
            ],
            'customer_details' => [
                'first_name' => $customer->user?->name ?? 'Customer ReValue',
                'email' => $customer->user?->email ?? 'customer@revalue.id',
            ],
        ];

        $snapResult = $this->midtransService->createSnapTransaction($params);
        $transaction->update([
            'midtrans_order_id' => $orderId,
            'snap_url' => $snapResult['redirect_url'] ?? $transaction->snap_url,
        ]);

        return $snapResult;
    }
}
