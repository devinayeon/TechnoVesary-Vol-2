<?php

use App\Enums\PickupStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;

uses(DatabaseTransactions::class);

test('customer dapat mengakses halaman profil dengan tab achievements dan menerima data yang benar', function () {
    $user = User::factory()->create([
        'role' => UserRole::CUSTOMER,
        'email_verified_at' => now(),
    ]);

    $customer = Customer::create([
        'user_id' => $user->id,
        'points' => 1500,
        'is_member' => false,
    ]);

    $category = WasteCategory::firstOrCreate(
        ['name' => 'Kardus'],
        [
            'description' => 'Kardus dan kertas',
            'points_per_kg' => 1000,
            'is_active' => true,
        ]
    );

    // Buat 2 penjemputan completed
    $deposit1 = WasteDeposit::create([
        'customer_id' => $customer->id,
        'status' => PickupStatus::COMPLETED,
        'address' => 'Jl. Kebersihan',
        'latitude' => -6.2,
        'longitude' => 106.8,
    ]);

    WasteDepositDetail::create([
        'waste_deposit_id' => $deposit1->id,
        'waste_category_id' => $category->id,
        'weight' => 5.5,
        'points_per_kg' => 1000,
        'subtotal_points' => 5500,
    ]);

    $deposit2 = WasteDeposit::create([
        'customer_id' => $customer->id,
        'status' => PickupStatus::COMPLETED,
        'address' => 'Jl. Kebersihan 2',
        'latitude' => -6.2,
        'longitude' => 106.8,
    ]);

    WasteDepositDetail::create([
        'waste_deposit_id' => $deposit2->id,
        'waste_category_id' => $category->id,
        'weight' => 6.5,
        'points_per_kg' => 1000,
        'subtotal_points' => 6500,
    ]);

    $response = $this->actingAs($user)->get('/profile?tab=achievements');

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Profile/Edit')
        ->has('achievements', fn (Assert $achievements) => $achievements
            ->where('summary.completed_pickups', 2)
            ->where('summary.total_weight_raw', 12)
            ->has('all_badges', 6)
            ->has('earned_badges')
            ->has('in_progress_badges')
            ->etc()
        )
    );
});

test('non customer tetap dapat mengakses halaman profil dengan normal', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/profile');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Profile/Edit')
        ->where('achievements', null)
        ->etc()
    );
});
