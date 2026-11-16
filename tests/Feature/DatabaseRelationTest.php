<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PickupStatus;
use App\Enums\PointType;
use App\Enums\TransactionStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\SubscriptionTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WasteDeposit;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('relasi one to one profil admin driver dan customer berfungsi', function () {
    $adminUser = User::where('email', 'admin@banksampah.test')->first();
    $driverUser = User::where('email', 'driver@banksampah.test')->first();
    $customerUser = User::where('email', 'nasabah@banksampah.test')->first();

    expect($adminUser)->not->toBeNull()
        ->and($adminUser->admin)->toBeInstanceOf(Admin::class)
        ->and($adminUser->admin->user->id)->toBe($adminUser->id);

    expect($driverUser)->not->toBeNull()
        ->and($driverUser->driver)->toBeInstanceOf(Driver::class)
        ->and($driverUser->driver->is_available)->toBeTrue();

    expect($customerUser)->not->toBeNull()
        ->and($customerUser->customer)->toBeInstanceOf(Customer::class)
        ->and($customerUser->customer->points)->toBeGreaterThanOrEqual(0);
});

test('relasi waste deposit ke detail dan waste category berfungsi dengan perhitungan poin benar', function () {
    $deposit = WasteDeposit::with('details.wasteCategory')->first();

    expect($deposit)->not->toBeNull()
        ->and($deposit->status)->toBe(PickupStatus::COMPLETED)
        ->and($deposit->customer)->toBeInstanceOf(Customer::class)
        ->and($deposit->driver)->toBeInstanceOf(Driver::class)
        ->and($deposit->details)->not->toBeEmpty();

    foreach ($deposit->details as $detail) {
        expect($detail->wasteCategory)->not->toBeNull()
            ->and((int) $detail->subtotal_points)->toBe((int) ($detail->weight * $detail->points_per_kg));
    }
});

test('skenario transaksi hybrid split payment dan full points berfungsi secara tepat', function () {
    // 1. Transaksi Split Payment (Poin + Midtrans)
    $splitTx = Transaction::where('payment_method', PaymentMethod::MIDTRANS)->first();
    expect($splitTx)->not->toBeNull()
        ->and($splitTx->points_used)->toBeGreaterThan(0)
        ->and($splitTx->payable_amount)->toBe($splitTx->total_amount - $splitTx->points_discount_amount)
        ->and($splitTx->snap_url)->not->toBeNull();

    // 2. Transaksi Full Points (Poin Penuh tanpa Midtrans)
    $fullPointsTx = Transaction::where('payment_method', PaymentMethod::POINTS)->first();
    expect($fullPointsTx)->not->toBeNull()
        ->and($fullPointsTx->payable_amount)->toBe(0)
        ->and($fullPointsTx->snap_url)->toBeNull();
});

test('relasi produk dan artikel dengan admin terhubung dengan baik', function () {
    $product = Product::with('admin')->first();
    $article = Article::with(['admin', 'category'])->first();

    expect($product)->not->toBeNull()
        ->and($product->admin)->toBeInstanceOf(Admin::class);

    expect($article)->not->toBeNull()
        ->and($article->admin)->toBeInstanceOf(Admin::class)
        ->and($article->category)->not->toBeNull();
});

test('mutasi poin riwayat tercatat dengan tipe enum yang valid', function () {
    $customer = Customer::with('pointHistories')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->pointHistories)->not->toBeEmpty();

    foreach ($customer->pointHistories as $history) {
        expect($history->type)->toBeInstanceOf(PointType::class)
            ->and($history->amount)->toBeGreaterThan(0);
    }
});
