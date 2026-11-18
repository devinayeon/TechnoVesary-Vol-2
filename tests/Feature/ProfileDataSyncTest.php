<?php

use App\Enums\UserRole;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(DatabaseTransactions::class);

test('sinkronisasi pembaruan kata sandi berhasil tersimpan dan dapat digunakan login', function () {
    $user = User::factory()->create([
        'password' => bcrypt('PasswordLama123!'),
    ]);

    $response = $this->actingAs($user)->put('/password', [
        'current_password' => 'PasswordLama123!',
        'password' => 'PasswordBaru456!',
        'password_confirmation' => 'PasswordBaru456!',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('status', 'password-updated');

    $user->refresh();

    // Password lama harus gagal, password baru harus berhasil
    expect(Hash::check('PasswordLama123!', $user->password))->toBeFalse();
    expect(Hash::check('PasswordBaru456!', $user->password))->toBeTrue();
});

test('sinkronisasi unggah foto profil tersimpan di storage dan database', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'photo' => null,
    ]);

    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200)->size(500);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => $file,
    ]);

    $response->assertSessionHasNoErrors();
    $user->refresh();

    expect($user->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($user->photo);

    // Tes hapus foto profil
    $oldPhoto = $user->photo;
    $deleteResponse = $this->actingAs($user)->patch('/profile', [
        'remove_photo' => true,
    ]);

    $deleteResponse->assertSessionHasNoErrors();
    $user->refresh();

    expect($user->photo)->toBeNull();
    Storage::disk('public')->assertMissing($oldPhoto);
});

test('sinkronisasi alamat dan titik koordinat tersimpan akurat ke database', function () {
    $user = User::factory()->create([
        'address' => 'Alamat Awal',
        'latitude' => -6.20000000,
        'longitude' => 106.81666600,
    ]);

    $response = $this->actingAs($user)->patch('/profile', [
        'address' => 'JL. Kalimantan No. 37, Sumbersari, Jember',
        'latitude' => -8.16421234,
        'longitude' => 113.71715678,
    ]);

    $response->assertSessionHasNoErrors();
    $user->refresh();

    expect($user->address)->toBe('JL. Kalimantan No. 37, Sumbersari, Jember');
    expect((float) $user->latitude)->toBe(-8.16421234);
    expect((float) $user->longitude)->toBe(113.71715678);
});

test('sinkronisasi kesiapan tugas driver tersinkronisasi ke tabel drivers', function () {
    $user = User::factory()->create([
        'role' => UserRole::DRIVER,
    ]);

    $driver = Driver::create([
        'user_id' => $user->id,
        'is_available' => true,
    ]);

    // Driver switch ke istirahat
    $response = $this->actingAs($user)->patch('/profile', [
        'is_available' => false,
    ]);

    $response->assertSessionHasNoErrors();
    expect($driver->fresh()->is_available)->toBeFalse();

    // Driver switch kembali ke siap bertugas
    $response2 = $this->actingAs($user)->patch('/profile', [
        'is_available' => true,
    ]);

    $response2->assertSessionHasNoErrors();
    expect($driver->fresh()->is_available)->toBeTrue();
});
