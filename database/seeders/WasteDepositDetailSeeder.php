<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use App\Models\WasteDeposit;
use App\Models\WasteDepositDetail;
use Illuminate\Database\Seeder;

class WasteDepositDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deposit = WasteDeposit::first();
        $petCategory = WasteCategory::where('name', 'like', '%PET%')->first();
        $kardusCategory = WasteCategory::where('name', 'like', '%Kardus%')->first();

        if (! $deposit || ! $petCategory || ! $kardusCategory) {
            return;
        }

        WasteDepositDetail::updateOrCreate(
            [
                'waste_deposit_id' => $deposit->id,
                'waste_category_id' => $petCategory->id,
            ],
            [
                'points_per_kg' => $petCategory->points_per_kg,
                'weight' => 12.50,
                'subtotal_points' => 12.50 * $petCategory->points_per_kg, // 43750
            ]
        );

        WasteDepositDetail::updateOrCreate(
            [
                'waste_deposit_id' => $deposit->id,
                'waste_category_id' => $kardusCategory->id,
            ],
            [
                'points_per_kg' => $kardusCategory->points_per_kg,
                'weight' => 8.00,
                'subtotal_points' => 8.00 * $kardusCategory->points_per_kg, // 17600
            ]
        );
    }
}
