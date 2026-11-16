<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Seeder;

class TransactionDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tx1 = Transaction::where('total_amount', 95000)->first();
        $tx2 = Transaction::where('total_amount', 20000)->first();

        $totebag = Product::where('name', 'like', '%Totebag%')->first();
        $kompos = Product::where('name', 'like', '%Pupuk%')->first();
        $pot = Product::where('name', 'like', '%Pot%')->first();

        if ($tx1 && $totebag && $kompos) {
            TransactionDetail::updateOrCreate(
                [
                    'transaction_id' => $tx1->id,
                    'product_id' => $totebag->id,
                ],
                [
                    'quantity' => 2,
                    'unit_price' => $totebag->price_rupiah, // 35000 * 2 = 70000
                ]
            );

            TransactionDetail::updateOrCreate(
                [
                    'transaction_id' => $tx1->id,
                    'product_id' => $kompos->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => $kompos->price_rupiah, // 25000 * 1 = 25000
                ]
            );
        }

        if ($tx2 && $pot) {
            TransactionDetail::updateOrCreate(
                [
                    'transaction_id' => $tx2->id,
                    'product_id' => $pot->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => $pot->price_rupiah, // 20000 * 1 = 20000
                ]
            );
        }
    }
}
