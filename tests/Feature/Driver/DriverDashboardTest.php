<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PickupStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WasteDeposit;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('driver dapat melihat dashboard dengan data riil dari database', function () {
    $driverUser = User::factory()->create([
        'role' => UserRole::DRIVER,
        'email_verified_at' => now(),
    ]);

    $driver = Driver::create([
        'user_id' => $driverUser->id,
        'is_available' => true,
    ]);

    $customerUser = User::factory()->create([
        'role' => UserRole::CUSTOMER,
        'email_verified_at' => now(),
    ]);

    $customer = Customer::create([
        'user_id' => $customerUser->id,
        'points' => 0,
        'is_member' => false,
    ]);

    // Buat 1 penjemputan aktif untuk driver
    WasteDeposit::create([
        'customer_id' => $customer->id,
        'driver_id' => $driver->id,
        'status' => PickupStatus::ON_THE_WAY,
        'address' => 'Jl. Kebon Jeruk No. 5',
        'latitude' => -8.1739,
        'longitude' => 113.6878,
    ]);

    // Buat 1 pengantaran produk aktif untuk driver
    Transaction::create([
        'customer_id' => $customer->id,
        'driver_id' => $driver->id,
        'total_amount' => 50000,
        'points_used' => 0,
        'points_discount_amount' => 0,
        'payable_amount' => 50000,
        'payment_method' => PaymentMethod::MIDTRANS,
        'status' => OrderStatus::DELIVERING,
        'delivery_address' => 'Jl. Kebon Jeruk No. 5',
    ]);

    $response = $this->actingAs($driverUser)->get('/driver/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Driver/Dashboard')
        ->has('metrics.activeTasks')
        ->has('metrics.weightCollected')
        ->has('metrics.deliveriesCompleted')
        ->has('tasks')
        ->has('progress')
        ->where('isAvailable', true)
        ->where('metrics.activeTasks.value', '2 Tugas')
    );
});
