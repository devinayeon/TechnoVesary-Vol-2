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
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->bigInteger('total_amount')->comment('Total biaya langganan');
            $table->bigInteger('points_used')->default(0)->comment('Poin yang ditukarkan nasabah');
            $table->bigInteger('points_discount_amount')->default(0)->comment('Nominal diskon rupiah dari poin');
            $table->bigInteger('payable_amount')->comment('Sisa rupiah yang dibayar via Midtrans (0 jika poin penuh)');
            $table->enum('payment_method', ['midtrans', 'points'])->default('midtrans');
            $table->string('snap_url')->nullable()->comment('URL Snap Midtrans jika payable_amount > 0');
            $table->enum('status', ['pending', 'settled', 'failed', 'canceled'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
    }
};
