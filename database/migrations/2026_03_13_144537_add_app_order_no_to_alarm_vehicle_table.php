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
            $table->string('order_no')->default('')->comment('订单编号/预约号');
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
