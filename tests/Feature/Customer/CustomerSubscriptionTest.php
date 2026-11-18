<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionTransaction;
use App\Models\User;
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Pastikan base subscription tersedia dengan harga 30000
    $this->baseSub = Subscription::first();
    if (! $this->baseSub) {
        $this->baseSub = Subscription::create([
            'name' => 'ReValue+ Bulanan',
            'price' => 30000,
            'description' => 'Paket langganan dasar penjemputan sampah terpilah selama 30 hari.',
        ]);
    } else {
        $this->baseSub->update(['price' => 30000]);
    }

    // Buat customer user
    $this->user = User::factory()->create([
        'role' => UserRole::CUSTOMER,
        'email_verified_at' => now(),
    ]);

    $this->customer = Customer::create([
        'user_id' => $this->user->id,
        'points' => 100000,
        'is_member' => false,
        'membership_expires_at' => null,
    ]);
});

test('customer dapat melihat katalog paket langganan ReValue+', function () {
    $response = $this->actingAs($this->user)->get('/customer/subscription');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Customer/Subscription/Index')
        ->has('packages', 3)
        ->has('membership')
        ->where('customerPoints', 100000)
    );
});

test('customer dapat melihat halaman checkout dengan paket yang dipilih', function () {
    $response = $this->actingAs($this->user)->get('/customer/subscription/checkout?package=3_months');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Customer/Subscription/Checkout')
        ->where('package.key', '3_months')
        ->where('package.price', 90000)
        ->where('customerPoints', 100000)
    );
});

test('customer dapat mengaktifkan paket langganan secara instan menggunakan poin penuh', function () {
    // 1 Bulan seharga 30.000, customer punya 100.000 poin
    $response = $this->actingAs($this->user)->postJson('/customer/subscription/checkout', [
        'package_key' => '1_month',
        'use_points' => true,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'is_full_points' => true,
    ]);

    // Poin customer berkurang 30.000 (sisa 70.000)
    $this->customer->refresh();
    expect($this->customer->points)->toBe(70000);
    expect($this->customer->is_member)->toBeTrue();
    expect($this->customer->membership_expires_at)->not->toBeNull();

    // Transaksi tercatat dengan status settled
    $transaction = SubscriptionTransaction::where('customer_id', $this->customer->id)->latest()->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->status)->toBe(TransactionStatus::SETTLED);
    expect($transaction->points_used)->toBe(30000);
    expect($transaction->payable_amount)->toBe(0);
    expect($transaction->payment_method)->toBe(PaymentMethod::POINTS);
});

test('customer dapat membuat pesanan langganan via Midtrans ketika poin tidak mencukupi atau tidak digunakan', function () {
    $this->mock(MidtransService::class, function (MockInterface $mock) {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->andReturn([
                'token' => 'mock-snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-123',
            ]);
    });

    // Checkout tanpa menggunakan poin
    $response = $this->actingAs($this->user)->postJson('/customer/subscription/checkout', [
        'package_key' => '1_month',
        'use_points' => false,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'is_full_points' => false,
        'snap_token' => 'mock-snap-token-123',
    ]);

    $this->customer->refresh();
    expect($this->customer->points)->toBe(100000); // Poin tidak berkurang
    expect($this->customer->is_member)->toBeFalse(); // Belum aktif karena status pending

    $transaction = SubscriptionTransaction::where('customer_id', $this->customer->id)->latest()->first();
    expect($transaction->status)->toBe(TransactionStatus::PENDING);
    expect($transaction->payable_amount)->toBe(30000);
    expect($transaction->payment_method)->toBe(PaymentMethod::MIDTRANS);
});

test('customer dapat melihat riwayat langganan', function () {
    SubscriptionTransaction::create([
        'customer_id' => $this->customer->id,
        'subscription_id' => $this->baseSub->id,
        'package_key' => '3_months',
        'duration_months' => 3,
        'total_amount' => 90000,
        'points_used' => 0,
        'points_discount_amount' => 0,
        'payable_amount' => 90000,
        'payment_method' => PaymentMethod::MIDTRANS,
        'status' => TransactionStatus::PENDING,
    ]);

    $response = $this->actingAs($this->user)->get('/customer/subscription/history');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Customer/Subscription/History')
        ->has('transactions.data', 1)
    );
});

test('customer dapat membatalkan pesanan langganan pending dan poin dikembalikan', function () {
    // Kurangi poin seolah-olah dipotong saat checkout sebagian poin
    $this->customer->update(['points' => 80000]);

    $transaction = SubscriptionTransaction::create([
        'customer_id' => $this->customer->id,
        'subscription_id' => $this->baseSub->id,
        'package_key' => '1_month',
        'duration_months' => 1,
        'total_amount' => 30000,
        'points_used' => 20000,
        'points_discount_amount' => 20000,
        'payable_amount' => 10000,
        'payment_method' => PaymentMethod::MIDTRANS,
        'status' => TransactionStatus::PENDING,
    ]);

    $response = $this->actingAs($this->user)->post("/customer/subscription/history/{$transaction->id}/cancel");

    $response->assertRedirect();

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::CANCELED);

    // Poin 20.000 dikembalikan
    $this->customer->refresh();
    expect($this->customer->points)->toBe(100000);
});

test('webhook midtrans SUB order ID mengaktifkan langganan customer', function () {
    $transaction = SubscriptionTransaction::create([
        'customer_id' => $this->customer->id,
        'subscription_id' => $this->baseSub->id,
        'package_key' => '1_year',
        'duration_months' => 12,
        'total_amount' => 360000,
        'points_used' => 0,
        'points_discount_amount' => 0,
        'payable_amount' => 360000,
        'payment_method' => PaymentMethod::MIDTRANS,
        'status' => TransactionStatus::PENDING,
    ]);

    $orderId = "SUB-{$transaction->id}-".time();
    $transaction->update(['midtrans_order_id' => $orderId]);

    $this->mock(MidtransService::class, function (MockInterface $mock) {
        $mock->shouldReceive('isValidSignature')
            ->once()
            ->andReturn(true);
    });

    $payload = [
        'order_id' => $transaction->midtrans_order_id,
        'status_code' => '200',
        'gross_amount' => '360000.00',
        'signature_key' => 'mock-sig',
        'transaction_status' => 'settlement',
    ];

    $response = $this->postJson('/api/midtrans/notification', $payload);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::SETTLED);

    $this->customer->refresh();
    expect($this->customer->is_member)->toBeTrue();
    expect($this->customer->membership_expires_at)->not->toBeNull();
});
