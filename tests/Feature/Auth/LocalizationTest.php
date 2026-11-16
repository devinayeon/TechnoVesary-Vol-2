<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('pesan error login menggunakan bahasa indonesia yang mudah dipahami', function () {
    $response = $this->post('/login', [
        'email' => 'tidakada@banksampah.test',
        'password' => 'password-salah',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Kombinasi alamat email dan kata sandi yang Anda masukkan tidak sesuai.',
    ]);
});

test('pesan validasi registrasi kosong menggunakan bahasa indonesia', function () {
    $response = $this->post('/register', [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors([
        'name' => 'Kolom Nama lengkap wajib diisi.',
        'email' => 'Kolom Alamat email wajib diisi.',
        'password' => 'Kolom Kata sandi wajib diisi.',
    ]);
});
