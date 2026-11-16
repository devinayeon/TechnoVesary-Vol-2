<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = [
            [
                'name' => 'Paket Basic (Rumah Tangga)',
                'price' => 25000,
                'description' => 'Layanan penjemputan sampah terpilah 2x per bulan, penimbangan di tempat, dan bonus 200 poin sambutan.',
            ],
            [
                'name' => 'Paket Pro (Keluarga Peduli Lingkungan)',
                'price' => 50000,
                'description' => 'Layanan penjemputan 4x per bulan (mingguan), 1.5x multiplier poin reward, dan prioritas armada OSRM.',
            ],
            [
                'name' => 'Paket Komersial (Kafe & UMKM)',
                'price' => 150000,
                'description' => 'Penjemputan muatan besar hingga 100 Kg per minggu, laporan audit daur ulang berkala, dan verifikasi sertifikat hijau.',
            ],
        ];

        foreach ($subscriptions as $item) {
            Subscription::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
