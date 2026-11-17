<?php

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

test('non-admin (customer & driver) ditolak saat mengakses endpoint manajemen pengguna', function () {
    $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
    $driver = User::factory()->create(['role' => UserRole::DRIVER]);

    // Customer mencoba akses
    $this->actingAs($customer)->get('/admin/users')->assertStatus(403);
    $this->actingAs($customer)->post('/admin/users', [])->assertStatus(403);

    // Driver mencoba akses
    $this->actingAs($driver)->get('/admin/users')->assertStatus(403);
    $this->actingAs($driver)->post('/admin/users', [])->assertStatus(403);
});

test('admin dapat melihat halaman daftar pengguna dan form create', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)->get('/admin/users')->assertOk();
    $this->actingAs($admin)->get('/admin/users/create')->assertOk();
});

test('admin dapat menambah user baru dengan role driver dan tabel drivers otomatis terbuat', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Driver Baru',
        'email' => 'driver.baru@banksampah.test',
        'password' => 'Password123#',
        'password_confirmation' => 'Password123#',
        'role' => UserRole::DRIVER->value,
        'phone_number' => '081234567899',
        'address' => 'Jl. Armada No. 12',
        'is_available' => true,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'driver.baru@banksampah.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::DRIVER)
        ->and($user->driver)->not->toBeNull()
        ->and($user->driver)->toBeInstanceOf(Driver::class)
        ->and($user->driver->is_available)->toBeTrue();
});

test('admin dapat menambah user baru dengan role customer dan tabel customers otomatis terbuat', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Nasabah Baru',
        'email' => 'nasabah.baru@banksampah.test',
        'password' => 'Password123#',
        'password_confirmation' => 'Password123#',
        'role' => UserRole::CUSTOMER->value,
        'phone_number' => '081211223344',
        'address' => 'Jl. Mawar No. 5',
        'points' => 500,
        'is_member' => true,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'nasabah.baru@banksampah.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::CUSTOMER)
        ->and($user->customer)->not->toBeNull()
        ->and($user->customer)->toBeInstanceOf(Customer::class)
        ->and($user->customer->points)->toBe(500)
        ->and($user->customer->is_member)->toBeTrue();
});

test('admin dapat menambah user baru dengan role admin dan tabel admins otomatis terbuat', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Super Admin 2',
        'email' => 'admin2@banksampah.test',
        'password' => 'Password123#',
        'password_confirmation' => 'Password123#',
        'role' => UserRole::ADMIN->value,
        'phone_number' => '081299887766',
        'address' => 'Kantor Cabang',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'admin2@banksampah.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::ADMIN)
        ->and($user->admin)->not->toBeNull()
        ->and($user->admin)->toBeInstanceOf(Admin::class);
});

test('admin dapat memperbarui data pengguna termasuk mereset kata sandi jika lupa', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $targetUser = User::factory()->create([
        'name' => 'Nama Lama',
        'role' => UserRole::CUSTOMER,
        'password' => Hash::make('PasswordLama123#'),
    ]);
    Customer::create(['user_id' => $targetUser->id, 'points' => 100]);

    // Update dengan password baru (reset password oleh admin)
    $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
        'name' => 'Nama Baru Diperbarui',
        'email' => $targetUser->email,
        'password' => 'PasswordBaruReset123#',
        'password_confirmation' => 'PasswordBaruReset123#',
        'role' => UserRole::CUSTOMER->value,
        'phone_number' => '089988776655',
        'points' => 250,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.users.index'));

    $targetUser->refresh();
    expect($targetUser->name)->toBe('Nama Baru Diperbarui')
        ->and($targetUser->phone_number)->toBe('089988776655')
        ->and($targetUser->customer->points)->toBe(250);

    // Verifikasi kata sandi baru bisa digunakan untuk autentikasi
    expect(auth()->attempt([
        'email' => $targetUser->email,
        'password' => 'PasswordBaruReset123#',
    ]))->toBeTrue();
});

test('kata sandi lama pengguna tidak berubah jika field password dikosongkan saat update', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $targetUser = User::factory()->create([
        'name' => 'User Tetap',
        'role' => UserRole::CUSTOMER,
        'password' => Hash::make('TetapPasswordLama123#'),
    ]);
    Customer::create(['user_id' => $targetUser->id]);

    $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
        'name' => 'User Ganti Nama Saja',
        'email' => $targetUser->email,
        'password' => '', // Dikosongkan
        'password_confirmation' => '',
        'role' => UserRole::CUSTOMER->value,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.users.index'));

    expect(auth()->attempt([
        'email' => $targetUser->email,
        'password' => 'TetapPasswordLama123#',
    ]))->toBeTrue();
});

test('admin dapat melakukan soft delete pada pengguna', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $targetUser = User::factory()->create(['role' => UserRole::CUSTOMER]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$targetUser->id}");

    $response->assertRedirect(route('admin.users.index'));

    expect($targetUser->fresh()->trashed())->toBeTrue()
        ->and(User::find($targetUser->id))->toBeNull()
        ->and(User::withTrashed()->find($targetUser->id))->not->toBeNull();
});

test('admin diblokir jika mencoba menghapus akunnya sendiri', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

    $response->assertSessionHasErrors('error');
    expect($admin->fresh()->trashed())->toBeFalse();
});

test('admin dapat memulihkan pengguna yang berada di status soft delete (restore)', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $targetUser = User::factory()->create(['role' => UserRole::CUSTOMER]);
    $targetUser->delete();

    expect($targetUser->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/restore");

    $response->assertSessionHasNoErrors();
    expect($targetUser->fresh()->trashed())->toBeFalse();
});
