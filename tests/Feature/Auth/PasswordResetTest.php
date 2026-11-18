<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;

uses(DatabaseTransactions::class);

test('halaman lupa password dapat ditampilkan', function () {
    $response = $this->get('/forgot-password');
    $response->assertStatus(200);
});

test('link reset password dapat dikirimkan ke email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('halaman reset password dapat ditampilkan dengan token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);
        $response->assertStatus(200);

        return true;
    });
});

test('password dapat direset dengan token valid', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'PasswordBaru123#',
            'password_confirmation' => 'PasswordBaru123#',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('login'));
        expect(auth()->attempt([
            'email' => $user->email,
            'password' => 'PasswordBaru123#',
        ]))->toBeTrue();

        return true;
    });
});
