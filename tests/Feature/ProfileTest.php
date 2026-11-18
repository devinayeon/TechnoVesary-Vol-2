<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('halaman profil dapat ditampilkan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
});

test('informasi profil dapat diperbarui', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Nama Baru Nasabah',
        'email' => 'namabaru@banksampah.test',
        'phone_number' => '081234567890',
        'address' => 'JL. Kalimantan No. 12 Jember',
        'latitude' => -8.1642,
        'longitude' => 113.7171,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Nama Baru Nasabah');
    expect($user->email)->toBe('namabaru@banksampah.test');
    expect($user->phone_number)->toBe('081234567890');
    expect($user->address)->toBe('JL. Kalimantan No. 12 Jember');
    expect((float) $user->latitude)->toBe(-8.1642);
    expect((float) $user->longitude)->toBe(113.7171);
    expect($user->email_verified_at)->toBeNull();
});

test('informasi profil dapat diperbarui menggunakan alias bahasa indonesia', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Nama Baru Alias',
        'email' => 'alias@banksampah.test',
        'nomor_telepon' => '089876543210',
        'alamat' => 'JL. Mastrip No. 5 Jember',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->nomor_telepon)->toBe('089876543210');
    expect($user->phone_number)->toBe('089876543210');
    expect($user->alamat)->toBe('JL. Mastrip No. 5 Jember');
    expect($user->address)->toBe('JL. Mastrip No. 5 Jember');
});

test('driver dapat mengubah status ketersediaan tugas', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::DRIVER,
    ]);
    \App\Models\Driver::create([
        'user_id' => $user->id,
        'is_available' => true,
    ]);

    $response = $this->actingAs($user)->patch('/profile', [
        'is_available' => false,
    ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->driver->is_available)->toBeFalse();
});

test('status verifikasi email tidak berubah jika email tidak diganti', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Nama Saja Berganti',
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('profile.edit'));

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('kata sandi pengguna dapat diperbarui', function () {
    $user = User::factory()->create([
        'password' => bcrypt('PasswordLama123#'),
    ]);

    $response = $this->actingAs($user)->put('/password', [
        'current_password' => 'PasswordLama123#',
        'password' => 'PasswordBaru456#',
        'password_confirmation' => 'PasswordBaru456#',
    ]);

    $response->assertSessionHasNoErrors();
    expect(auth()->attempt([
        'email' => $user->email,
        'password' => 'PasswordBaru456#',
    ]))->toBeTrue();
});

test('penghapusan akun mewajibkan kata sandi yang valid', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->actingAs($user)->from('/profile')->delete('/profile', [
        'password' => 'password-salah',
    ]);

    $response->assertSessionHasErrors('password');
    $response->assertRedirect('/profile');
    expect($user->fresh())->not->toBeNull();
});

test('akun dapat dihapus dengan memasukkan kata sandi yang benar', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/');

    $this->assertGuest();
    expect(User::find($user->id))->toBeNull();
});
