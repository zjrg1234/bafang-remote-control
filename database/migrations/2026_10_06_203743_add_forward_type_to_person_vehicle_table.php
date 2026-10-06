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
        Schema::table('person_vehicle', function (Blueprint $table) {
            $table->integer('forward_type')->default(0)->comment('车辆类型：1 一代机 2 二代机');
            $table->string('top_speed')->default('')->comment('最高时速');
            $table->integer('vehicle_sorting')->default(0)->comment('排序');
            $table->string('vehicle_introduction')->default('')->comment('车辆特点');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_vehicle', function (Blueprint $table) {
            //
        });
    }
};
