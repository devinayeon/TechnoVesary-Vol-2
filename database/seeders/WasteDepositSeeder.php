<?php

namespace Database\Seeders;

use App\Enums\PickupStatus;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\WasteDeposit;
use Illuminate\Database\Seeder;

class WasteDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        $driver = Driver::first();

        if (! $customer || ! $driver) {
            return;
        }

        WasteDeposit::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'address' => 'Perumahan Asri Lestari Blok B2 No. 14, Jakarta Selatan',
            ],
            [
                'driver_id' => $driver->id,
                'status' => PickupStatus::COMPLETED,
                'notes' => 'Sampah botol plastik sudah dikemasi rapi dalam 2 karung bersih.',
                'latitude' => -6.26149300,
                'longitude' => 106.81059900,
                'photo' => null,
                'pickup_proof_photo' => null,
                'pickup_at' => now()->subDays(2),
            ]
        );
    }
}
