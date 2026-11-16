<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin Bank Sampah
        User::updateOrCreate(
            ['email' => 'admin@banksampah.test'],
            [
                'name' => 'Admin Bank Sampah',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Driver Armada
        User::updateOrCreate(
            ['email' => 'driver@banksampah.test'],
            [
                'name' => 'Budi Santoso (Driver)',
                'password' => Hash::make('password'),
                'role' => UserRole::DRIVER,
                'email_verified_at' => now(),
            ]
        );

        // 3. Akun Nasabah / Customer
        User::updateOrCreate(
            ['email' => 'nasabah@banksampah.test'],
            [
                'name' => 'Siti Aminah (Nasabah)',
                'password' => Hash::make('password'),
                'role' => UserRole::CUSTOMER,
                'email_verified_at' => now(),
            ]
        );
    }
}
