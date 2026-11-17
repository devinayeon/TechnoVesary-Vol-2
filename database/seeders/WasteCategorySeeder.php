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
                'name' => 'Plastik PET (Botol Air Mineral Bersih)',
                'description' => 'Botol plastik bening/transparan bekas air mineral atau minuman ringan. Pastikan dalam kondisi bersih, kering, dan label plastik serta tutup sudah dilepaskan.',
                'points_per_kg' => 3500,
            ],
            [
                'name' => 'Plastik HDPE (Botol Shampo & Deterjen)',
                'description' => 'Wadah plastik tebal dan kaku bertanda segitiga daur ulang nomor 2, seperti jerigen minyak, botol sabun cair, shampo, dan botol pemutih yang telah dibilas.',
                'points_per_kg' => 4000,
            ],
            [
                'name' => 'Karton & Kardus Cokelat',
                'description' => 'Kardus kemasan cokelat berlapis (corrugated box) dan boks paket belanja online. Kondisi kering, tidak berjamur, bebas lakban tebal, dan dilipat pipih.',
                'points_per_kg' => 2200,
            ],
            [
                'name' => 'Kaleng Aluminium Minuman',
                'description' => 'Kaleng minuman ringan berbahan aluminium ringan (bukan seng/besi berkarat). Sebaiknya diremukkan/dipipihkan untuk menghemat ruang penyimpanan.',
                'points_per_kg' => 12000,
            ],
            [
                'name' => 'Minyak Jelantah (Used Cooking Oil)',
                'description' => 'Minyak goreng bekas pemakaian rumah tangga atau resto. Disaring dari remah sisa makanan dan disimpan dalam jerigen atau botol tertutup rapat.',
                'points_per_kg' => 6500,
            ],
            [
                'name' => 'Botol Kaca & Beling Utuh',
                'description' => 'Botol kaca sirup, kecap, atau minuman dalam kondisi utuh (tidak pecah/retak), telah dicuci bersih dan dikeringkan.',
                'points_per_kg' => 1000,
            ],
            [
                'name' => 'Besi Padat & Logam Rongsok',
                'description' => 'Pipa besi, kawat, peralatan dapur logam tak terpakai, paku, dan lempengan logam bebas dari material non-logam seperti kayu atau semen.',
                'points_per_kg' => 5500,
            ],
        ];

        foreach ($categories as $cat) {
            WasteCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
