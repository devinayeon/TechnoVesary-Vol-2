<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::first();
        if (! $admin) {
            return;
        }

        $products = [
            [
                'admin_id' => $admin->id,
                'name' => 'Totebag Daur Ulang Plastik Kemasan',
                'description' => 'Tas belanja tahan air karya pengrajin binaan bank sampah, dibuat dari anyaman bungkus kopi & deterjen terpilah.',
                'price_rupiah' => 35000,
                'price_points' => 3500,
                'photo' => null,
                'stock' => 50,
            ],
            [
                'admin_id' => $admin->id,
                'name' => 'Pupuk Kompos Organik Premium 5 Kg',
                'description' => 'Pupuk kaya nutrisi hasil fermentasi limbah dedaunan dan sisa makanan terkompos sempurna, cocok untuk tanaman hias dan sayur.',
                'price_rupiah' => 25000,
                'price_points' => 2500,
                'photo' => null,
                'stock' => 120,
            ],
            [
                'admin_id' => $admin->id,
                'name' => 'Lilin Aromaterapi Scented Oil (Lavender)',
                'description' => 'Lilin ramah lingkungan dari minyak jelantah terfiltrasi karbon aktif dengan aroma essential oil murni yang menenangkan.',
                'price_rupiah' => 45000,
                'price_points' => 4500,
                'photo' => null,
                'stock' => 40,
            ],
            [
                'admin_id' => $admin->id,
                'name' => 'Pot Tanaman Hias Ecobrick Solid',
                'description' => 'Pot bunga dekoratif tahan banting dengan pewarnaan alami, dibuat dari kompresi plastik daur ulang padat.',
                'price_rupiah' => 20000,
                'price_points' => 2000,
                'photo' => null,
                'stock' => 85,
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(['name' => $prod['name']], $prod);
        }
    }
}
