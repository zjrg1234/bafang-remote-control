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
        Schema::create('alarm_vehicle', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id')->default(0)->comment('车辆id');
            $table->integer('agent_id')->default(0)->comment('代理id');
            $table->string('war_zone_name')->default('')->comment('专区名称');
            $table->string('war_id')->default('')->comment('id');
            $table->string('text')->default('')->comment('内容');
            $table->integer('status')->default(0)->comment('状态：0未处理 1已处理');
            $table->index('agent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarm_vehicle');
    }
};
