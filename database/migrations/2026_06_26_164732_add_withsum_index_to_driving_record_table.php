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
        Schema::table('driving_record', function (Blueprint $table) {
            $table->index(['venue_id', 'reservation_status', 'order_time'], 'idx_venue_status_time');
        });

        Schema::table('vehicle', function (Blueprint $table) {
            $table->index(['venue_id', 'vehicle_state'], 'idx_venue_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driving_record', function (Blueprint $table) {
            $table->dropIndex('idx_venue_status_time');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('idx_venue_state');
        });
    }
};
