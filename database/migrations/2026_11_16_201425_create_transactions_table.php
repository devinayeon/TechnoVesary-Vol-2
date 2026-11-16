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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->bigInteger('total_amount')->comment('Total belanja sebelum diskon poin');
            $table->bigInteger('points_used')->default(0)->comment('Poin yang digunakan untuk potongan harga');
            $table->bigInteger('points_discount_amount')->default(0)->comment('Nominal rupiah pemotong dari poin');
            $table->bigInteger('payable_amount')->comment('Sisa rupiah yang dibayar via Midtrans (0 jika poin penuh)');
            $table->enum('payment_method', ['midtrans', 'points'])->default('midtrans');
            $table->enum('status', ['pending', 'processing', 'delivering', 'completed', 'canceled'])->default('pending');
            $table->string('snap_url')->nullable();
            $table->text('delivery_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('delivery_proof_photo')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
