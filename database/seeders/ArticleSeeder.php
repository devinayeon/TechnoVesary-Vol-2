<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::first();
        $cat1 = ArticleCategory::where('name', 'Tips Pemilahan Sampah')->first();
        $cat2 = ArticleCategory::where('name', 'Ekonomi Sirkular & Zero Waste')->first();

        if (! $admin || ! $cat1 || ! $cat2) {
            return;
        }

        $articles = [
            [
                'admin_id' => $admin->id,
                'category_id' => $cat1->id,
                'title' => 'Panduan Praktis Memilah Sampah Anorganik dari Dapur Rumah',
                'slug' => Str::slug('Panduan Praktis Memilah Sampah Anorganik dari Dapur Rumah'),
                'photo' => null,
                'content' => 'Langkah awal memulai gaya hidup ramah lingkungan dimulai dari pemilahan sampah di sumbernya. Pisahkan antara sampah basah organik dengan sampah anorganik kering seperti botol plastik PET dan kardus.',
            ],
            [
                'admin_id' => $admin->id,
                'category_id' => $cat2->id,
                'title' => 'Mengenal Konsep Ekonomi Sirkular: Mengubah Limbah Menjadi Nilai Ekonomi',
                'slug' => Str::slug('Mengenal Konsep Ekonomi Sirkular Mengubah Limbah Menjadi Nilai Ekonomi'),
                'photo' => null,
                'content' => 'Ekonomi sirkular berfokus pada perpanjangan siklus hidup material melalui daur ulang, perbaikan, dan pemanfaatan kembali guna meminimalisasi timbunan residu ke Tempat Pembuangan Akhir (TPA).',
            ],
        ];

        foreach ($articles as $art) {
            Article::updateOrCreate(['slug' => $art['slug']], $art);
        }
    }
}
