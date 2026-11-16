<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use Illuminate\Database\Seeder;

class WasteCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Plastik PET (Botol Air Mineral Bersih)', 'points_per_kg' => 3500],
            ['name' => 'Plastik HDPE (Botol Shampo & Deterjen)', 'points_per_kg' => 4000],
            ['name' => 'Karton & Kardus Cokelat', 'points_per_kg' => 2200],
            ['name' => 'Kaleng Aluminium Minuman', 'points_per_kg' => 12000],
            ['name' => 'Minyak Jelantah (Used Cooking Oil)', 'points_per_kg' => 6500],
            ['name' => 'Botol Kaca & Beling Utuh', 'points_per_kg' => 1000],
            ['name' => 'Besi Padat & Logam Rongsok', 'points_per_kg' => 5500],
        ];

        foreach ($categories as $cat) {
            WasteCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
