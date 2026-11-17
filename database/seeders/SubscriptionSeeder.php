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
                'name' => 'Paket Revalue +',
                'price' => 25000,
                'description' => 'Beli Revalue+ untuk melakukan penjemputan dan mendapatkan poin',
            ],
        ];

        foreach ($subscriptions as $item) {
            Subscription::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
