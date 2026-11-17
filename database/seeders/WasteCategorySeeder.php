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
            [
                'name' => 'Plastik PET ',
                'description' => 'Botol plastik bening/transparan bekas air mineral atau minuman ringan. Pastikan dalam kondisi bersih, kering, dan label plastik serta tutup sudah dilepaskan.',
                'points_per_kg' => 3500,
            ],
            [
                'name' => 'Kertas & Kardus',
                'description' => 'Kertas Dan Kardus',
                'points_per_kg' => 4000,
            ],
            [
                'name' => 'Logam & Kaleng',
                'description' => 'Logam dan Kaleng',
                'points_per_kg' => 3000,
            ],
            [
                'name' => 'Kaca',
                'description' => 'Kaca',
                'points_per_kg' => 4500,
            ],
            [
                'name' => 'Sachet',
                'description' => 'Sachet',
                'points_per_kg' => 5000,
            ],
        ];

        foreach ($categories as $cat) {
            WasteCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
