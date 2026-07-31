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
        Schema::table('alarm_vehicle', function (Blueprint $table) {
            $table->integer('uid')->default(0)->after('vehicle_id')->comment('用户uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alarm_vehicle', function (Blueprint $table) {
            //
        });
    }
};
