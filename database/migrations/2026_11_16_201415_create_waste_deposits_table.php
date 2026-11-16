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
        Schema::create('waste_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->enum('status', ['pending', 'on_the_way', 'picked_up', 'completed', 'canceled'])->default('pending');
            $table->text('address');
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 8)->comment('Koordinat titik jemput OSM');
            $table->decimal('longitude', 11, 8)->comment('Koordinat titik jemput OSM');
            $table->string('photo')->nullable()->comment('Foto sampah nasabah saat request');
            $table->string('pickup_proof_photo')->nullable()->comment('Bukti timbangan/jemput oleh driver');
            $table->timestamp('pickup_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_deposits');
    }
};
