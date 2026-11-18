<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_transactions', function (Blueprint $table) {
            $table->string('package_key', 50)->nullable()->after('subscription_id')->comment('Key paket: 1_month, 3_months, 1_year');
            $table->unsignedInteger('duration_months')->default(1)->after('package_key')->comment('Durasi masa aktif dalam bulan');
            $table->string('midtrans_order_id')->nullable()->after('snap_url')->comment('Order ID resmi di Midtrans (SUB-xxx)');
            $table->string('snap_token')->nullable()->after('snap_url')->comment('Token Snap Midtrans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_transactions', function (Blueprint $table) {
            $table->dropColumn(['package_key', 'duration_months', 'midtrans_order_id', 'snap_token']);
        });
    }
};
