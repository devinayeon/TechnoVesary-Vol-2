<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        $driver = Driver::first();

        if (! $customer || ! $driver) {
            return;
        }

        // Transaksi 1: Split Payment (Midtrans dengan potongan poin)
        Transaction::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'delivery_address' => 'Perumahan Asri Lestari Blok B2 No. 14, Jakarta Selatan',
                'total_amount' => 95000,
            ],
            [
                'driver_id' => $driver->id,
                'points_used' => 15000,
                'points_discount_amount' => 15000,
                'payable_amount' => 80000, // 95000 - 15000
                'payment_method' => PaymentMethod::MIDTRANS,
                'status' => OrderStatus::COMPLETED,
                'snap_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/demo-token-order-1',
                'latitude' => -6.26149300,
                'longitude' => 106.81059900,
                'delivery_proof_photo' => null,
                'settled_at' => now()->subDays(3),
                'delivered_at' => now()->subDays(2),
            ]
        );

        // Transaksi 2: Full Poin (Lunas 100% menggunakan poin tanpa Midtrans)
        Transaction::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'delivery_address' => 'Perumahan Asri Lestari Blok B2 No. 14, Jakarta Selatan',
                'total_amount' => 20000,
            ],
            [
                'driver_id' => $driver->id,
                'points_used' => 20000,
                'points_discount_amount' => 20000,
                'payable_amount' => 0, // Rp 0
                'payment_method' => PaymentMethod::POINTS,
                'status' => OrderStatus::DELIVERING,
                'snap_url' => null, // Tidak perlu Midtrans
                'latitude' => -6.26149300,
                'longitude' => 106.81059900,
                'delivery_proof_photo' => null,
                'settled_at' => now()->subDay(),
                'delivered_at' => null,
            ]
        );
    }
}
