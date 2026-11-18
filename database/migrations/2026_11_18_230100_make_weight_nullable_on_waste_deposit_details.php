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
        Schema::table('waste_deposit_details', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable()->change()
                ->comment('Berat timbangan dalam Kg (null sebelum ditimbang driver)');
            $table->bigInteger('subtotal_points')->nullable()->change()
                ->comment('weight * points_per_kg (null sebelum ditimbang driver)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_deposit_details', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable(false)->change();
            $table->bigInteger('subtotal_points')->nullable(false)->change();
        });
    }
};
