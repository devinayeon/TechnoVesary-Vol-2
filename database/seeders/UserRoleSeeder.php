<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Driver;
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
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@banksampah.test'],
            [
                'name' => 'Admin Bank Sampah',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
                'phone_number' => '081234567890',
                'address' => 'Kantor Pusat Bank Sampah Digital, Jl. Kebersihan No. 10, Jakarta Pusat',
                'latitude' => -6.17539240,
                'longitude' => 106.82715280,
                'email_verified_at' => now(),
            ]
        );

        Admin::updateOrCreate(
            ['user_id' => $adminUser->id]
        );

        // 2. Akun Driver Armada
        $driverUser = User::updateOrCreate(
            ['email' => 'driver@banksampah.test'],
            [
                'name' => 'Budi Santoso (Driver)',
                'password' => Hash::make('password'),
                'role' => UserRole::DRIVER,
                'phone_number' => '081298765432',
                'address' => 'Pos Armada Wilayah Barat, Jl. Logistik No. 4, Jakarta Barat',
                'latitude' => -6.16832900,
                'longitude' => 106.75884900,
                'email_verified_at' => now(),
            ]
        );

        Driver::updateOrCreate(
            ['user_id' => $driverUser->id],
            [
                'is_available' => true,
            ]
        );

        // 3. Akun Nasabah / Customer
        $customerUser = User::updateOrCreate(
            ['email' => 'nasabah@banksampah.test'],
            [
                'name' => 'Siti Aminah (Nasabah)',
                'password' => Hash::make('password'),
                'role' => UserRole::CUSTOMER,
                'phone_number' => '081311223344',
                'address' => 'Perumahan Asri Lestari Blok B2 No. 14, Jakarta Selatan',
                'latitude' => -6.26149300,
                'longitude' => 106.81059900,
                'email_verified_at' => now(),
            ]
        );

        Customer::updateOrCreate(
            ['user_id' => $customerUser->id],
            [
                'points' => 25000,
                'is_member' => true,
                'membership_expires_at' => now()->addMonths(6),
            ]
        );
    }
}
