<?php

namespace Database\Seeders;

use App\Enums\PointType;
use App\Models\Customer;
use App\Models\PointHistory;
use Illuminate\Database\Seeder;

class PointHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        if (! $customer) {
            return;
        }

        $histories = [
            [
                'customer_id' => $customer->id,
                'type' => PointType::IN,
                'amount' => 61350,
                'source' => 'Setor Sampah Terpilah #1 (12.5 Kg PET & 8 Kg Kardus)',
            ],
            [
                'customer_id' => $customer->id,
                'type' => PointType::OUT,
                'amount' => 15000,
                'source' => 'Potongan Diskon Belanja Produk Upcycle #1',
            ],
            [
                'customer_id' => $customer->id,
                'type' => PointType::OUT,
                'amount' => 21350,
                'source' => 'Potongan Tagihan Langganan Paket Membership Pro #1',
            ],
        ];

        foreach ($histories as $item) {
            PointHistory::updateOrCreate(
                [
                    'customer_id' => $item['customer_id'],
                    'source' => $item['source'],
                ],
                $item
            );
        }
    }
}
