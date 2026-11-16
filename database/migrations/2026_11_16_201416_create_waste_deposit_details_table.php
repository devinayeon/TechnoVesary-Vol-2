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
        Schema::create('waste_deposit_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_deposit_id')->constrained('waste_deposits')->cascadeOnDelete();
            $table->foreignId('waste_category_id')->constrained('waste_categories')->cascadeOnDelete();
            $table->bigInteger('points_per_kg')->comment('Snapshot poin per kg saat transaksi');
            $table->decimal('weight', 8, 2)->comment('Berat timbangan dalam Kg');
            $table->bigInteger('subtotal_points')->comment('weight * points_per_kg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_deposit_details');
    }
};
