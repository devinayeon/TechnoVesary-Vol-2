<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            SubscriptionSeeder::class,
            WasteCategorySeeder::class,
            ArticleCategorySeeder::class,
            ArticleSeeder::class,
            ProductSeeder::class,
            WasteDepositSeeder::class,
            WasteDepositDetailSeeder::class,
            TransactionSeeder::class,
            TransactionDetailSeeder::class,
            PointHistorySeeder::class,
            SubscriptionTransactionSeeder::class,
        ]);
    }
}
