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
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id(); //车辆id
            $table->integer('agent_id')->default(0)->comment('代理商id');
            $table->integer('venue_id')->default(0)->comment('场地id');
            $table->integer('vehicle_type')->default(0)->comment('车辆类型：10-19四驱车、20-29挖机、30-39推土机、（后续顺延）');
            $table->string('vehicle_image')->default('')->comment('车辆图片');
            $table->string('vehicle_name')->default('')->comment('车辆名称');
            $table->integer('battery_time')->default(0)->comment('车辆每分钟消费电池或者能量');
            $table->string('battery')->default('')->comment('电池');
            $table->string('vehicle_introduction')->default('')->comment('车辆特点');
            $table->string('top_speed')->default('')->comment('最高时速');
            $table->string('front_camera')->default('')->comment('前置摄像头编码');
            $table->string('rear_camera')->default('')->comment('后置摄像头编码');
            $table->string('transmitter_id')->default('')->comment('发射机');
            $table->string('receiver_id')->default('')->comment('接收机');
            $table->integer('vehicle_sorting')->default(0)->comment('排序');
            $table->integer('status')->default(0)->comment('0：未上架 1：已上架');
            $table->integer('vehicle_state')->default(0)->comment('车辆状态：0离线 1在线空闲中 2在线驾驶中');
            $table->string('vehicle_battery')->default('')->comment('车辆电量');
            $table->string('password')->default('')->comment('车辆加密');
            $table->integer('is_password')->default(0)->comment('车辆是否加密');
            $table->integer('forward_type')->default(0)->comment('车辆类型：1 一代机 2 二代机');
            $table->index('venue_id');
            $table->index('agent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle');
    }
};
