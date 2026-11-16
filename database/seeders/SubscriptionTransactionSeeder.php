<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionTransaction;
use Illuminate\Database\Seeder;

class SubscriptionTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        $subscription = Subscription::where('name', 'like', '%Pro%')->first();

        if (! $customer || ! $subscription) {
            return;
        }

        SubscriptionTransaction::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
            ],
            [
                'total_amount' => $subscription->price, // 50000
                'points_used' => 21350,
                'points_discount_amount' => 21350,
                'payable_amount' => 28650, // 50000 - 21350 dibayar via Midtrans
                'payment_method' => PaymentMethod::MIDTRANS,
                'snap_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/demo-token-subscription-1',
                'status' => TransactionStatus::SETTLED,
                'settled_at' => now()->subDays(5),
                'canceled_at' => null,
            ]
        );
    }
}
