<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('halaman login dapat diakses oleh guest', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('halaman register dapat diakses oleh guest', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});

test('admin yang login diarahkan ke admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('admin.dashboard'));
});

test('driver yang login diarahkan ke driver dashboard', function () {
    $driver = User::factory()->create([
        'role' => UserRole::DRIVER,
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $driver->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($driver);
    $response->assertRedirect(route('driver.dashboard'));
});

test('nasabah customer yang login diarahkan ke customer dashboard', function () {
    $customer = User::factory()->create([
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($customer);
    $response->assertRedirect(route('customer.dashboard'));
});

test('nasabah tidak dapat mengakses admin dashboard dan menerima respon 403', function () {
    $customer = User::factory()->create([
        'role' => UserRole::CUSTOMER,
    ]);

    $response = $this->actingAs($customer)->get('/admin/dashboard');
    $response->assertStatus(403);
});

test('driver tidak dapat mengakses customer dashboard dan menerima respon 403', function () {
    $driver = User::factory()->create([
        'role' => UserRole::DRIVER,
    ]);

    $response = $this->actingAs($driver)->get('/customer/dashboard');
    $response->assertStatus(403);
});

test('registrasi akun baru otomatis menjadi customer dan diarahkan ke layar verifikasi email', function () {
    $email = 'nasabah.baru.'.uniqid().'@banksampah.test';

    $response = $this->post('/register', [
        'name' => 'Nasabah Baru',
        'email' => $email,
        'password' => 'Password123#',
        'password_confirmation' => 'Password123#',
    ]);

    $this->assertAuthenticated();
    $newUser = User::where('email', $email)->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->role)->toBe(UserRole::CUSTOMER);
    expect($newUser->customer)->not->toBeNull();
    expect($newUser->customer->points)->toBe(0);
    expect($newUser->customer->is_member)->toBeFalse();
    $response->assertRedirect(route('verification.notice'));
});
