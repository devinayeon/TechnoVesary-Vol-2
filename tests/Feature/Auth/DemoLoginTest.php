<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('guest dapat melakukan demo login sebagai admin dan diarahkan ke admin dashboard', function () {
    $admin = User::firstOrCreate(
        ['email' => 'admin@banksampah.test'],
        [
            'name' => 'Admin Bank Sampah',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]
    );

    $response = $this->post('/demo-login', [
        'role' => 'admin',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('admin.dashboard'));
});

test('guest dapat melakukan demo login sebagai driver dan diarahkan ke driver dashboard', function () {
    $driver = User::firstOrCreate(
        ['email' => 'driver@banksampah.test'],
        [
            'name' => 'Budi Santoso (Driver)',
            'password' => bcrypt('password'),
            'role' => UserRole::DRIVER,
            'email_verified_at' => now(),
        ]
    );

    $response = $this->post('/demo-login', [
        'role' => 'driver',
    ]);

    $this->assertAuthenticatedAs($driver);
    $response->assertRedirect(route('driver.dashboard'));
});

test('guest dapat melakukan demo login sebagai customer dan diarahkan ke customer dashboard', function () {
    $customer = User::firstOrCreate(
        ['email' => 'nasabah@banksampah.test'],
        [
            'name' => 'Siti Aminah (Nasabah)',
            'password' => bcrypt('password'),
            'role' => UserRole::CUSTOMER,
            'email_verified_at' => now(),
        ]
    );

    $response = $this->post('/demo-login', [
        'role' => 'customer',
    ]);

    $this->assertAuthenticatedAs($customer);
    $response->assertRedirect(route('customer.dashboard'));
});

test('demo login dengan role yang tidak valid akan ditolak', function () {
    $response = $this->post('/demo-login', [
        'role' => 'superadmin',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});

test('user yang sudah login tidak dapat mengakses demo login', function () {
    $user = User::factory()->create([
        'role' => UserRole::CUSTOMER,
    ]);

    $response = $this->actingAs($user)->post('/demo-login', [
        'role' => 'admin',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
});
