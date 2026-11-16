<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(DatabaseTransactions::class);

test('layar verifikasi email dapat dirender', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('user yang belum verifikasi email diblokir dari dashboard dan diarahkan ke verify-email', function () {
    $user = User::factory()->unverified()->create([
        'role' => UserRole::CUSTOMER,
    ]);

    $response = $this->actingAs($user)->get('/customer/dashboard');

    $response->assertRedirect(route('verification.notice'));
});

test('user yang login tapi belum verifikasi email langsung diarahkan ke verify-email', function () {
    $user = User::factory()->unverified()->create([
        'role' => UserRole::CUSTOMER,
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('verification.notice'));
});

test('email dapat diverifikasi menggunakan signed url yang valid', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('customer.dashboard', ['verified' => 1]));
});

test('email tidak diverifikasi jika hash salah', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email@example.com')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
