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
        Schema::table('drivers', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 8)->nullable()->after('is_available')
                ->comment('Koordinat terakhir driver untuk rute OSRM');
            $table->decimal('current_longitude', 11, 8)->nullable()->after('current_latitude')
                ->comment('Koordinat terakhir driver untuk rute OSRM');
            $table->timestamp('location_updated_at')->nullable()->after('current_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'current_latitude',
                'current_longitude',
                'location_updated_at',
            ]);
        });
    }
};
