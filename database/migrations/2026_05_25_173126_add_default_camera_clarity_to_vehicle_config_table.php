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
            $table->integer('default_camera_clarity')->default(4)->comment('视频清晰度默认值');
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
