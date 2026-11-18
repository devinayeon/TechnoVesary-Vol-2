<?php

use App\Enums\PickupStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('customer dapat melihat dashboard dengan data riil dari database', function () {
    $user = User::factory()->create([
        'role' => UserRole::CUSTOMER,
        'email_verified_at' => now(),
    ]);

    $customer = Customer::create([
        'user_id' => $user->id,
        'points' => 1250,
        'is_member' => true,
        'membership_expires_at' => now()->addDays(30),
    ]);

    $category = WasteCategory::firstOrCreate(
        ['name' => 'Plastik'],
        ['points_per_kg' => 100, 'description' => 'Sampah plastik daur ulang']
    );

    $deposit = WasteDeposit::create([
        'customer_id' => $customer->id,
        'status' => PickupStatus::COMPLETED,
        'address' => 'Jl. Mawar No. 10',
        'latitude' => -8.1739,
        'longitude' => 113.6878,
        'pickup_at' => now(),
    ]);

    WasteDepositDetail::create([
        'waste_deposit_id' => $deposit->id,
        'waste_category_id' => $category->id,
        'points_per_kg' => 100,
        'weight' => 15.5,
        'subtotal_points' => 1550,
    ]);

    $response = $this->actingAs($user)->get('/customer/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Customer/Dashboard')
        ->has('metrics.points')
        ->has('metrics.waste')
        ->has('metrics.membership')
        ->has('recentDeposits', 1)
        ->has('badges', 4)
        ->where('isMember', true)
        ->where('metrics.points.value', '1.250 Poin')
        ->where('metrics.waste.value', '15,5 Kg')
    );
});
