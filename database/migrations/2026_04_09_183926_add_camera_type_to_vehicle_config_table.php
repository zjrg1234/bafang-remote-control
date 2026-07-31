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
        Schema::table('vehicle_config', function (Blueprint $table) {
            $table->integer('camera_type')->default(1)->comment('1 新web摄像头 2旧摄像头');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_config', function (Blueprint $table) {
            //
        });
    }
};
