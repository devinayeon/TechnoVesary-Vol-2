<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tips Pemilahan Sampah'],
            ['name' => 'Ekonomi Sirkular & Zero Waste'],
            ['name' => 'Tutorial Kreasi Daur Ulang'],
            ['name' => 'Kebijakan & Inovasi Lingkungan'],
        ];

        foreach ($categories as $cat) {
            ArticleCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
