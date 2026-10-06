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
        Schema::create('person_vehicle', function (Blueprint $table) {
            $table->id(); //车辆id
            $table->integer('uid')->default(0)->comment('用户id');
            $table->integer('vehicle_type')->default(0)->comment('车辆类型：10-19四驱车、20-29挖机、30-39推土机、（后续顺延）');
            $table->integer('vehicle_sub_type')->default(0)->comment('子类型');
            $table->string('vehicle_image')->default('')->comment('车辆图片');
            $table->string('vehicle_name')->default('')->comment('车辆名称');
            $table->string('battery')->default('')->comment('电池');
            $table->string('front_camera')->default('')->comment('前置摄像头编码');
            $table->string('rear_camera')->default('')->comment('后置摄像头编码');
            $table->string('transmitter_id')->default('')->comment('发射机');
            $table->string('receiver_id')->default('')->comment('接收机');
            $table->integer('vehicle_state')->default(0)->comment('车辆状态：0离线 1在线空闲中 2在线驾驶中');
            $table->string('vehicle_battery')->default('')->comment('车辆电量');
            $table->integer('camera_type')->default(1)->comment('1 新web摄像头 2旧摄像头');
            $table->integer('default_camera_clarity')->default(4)->comment('视频清晰度默认值');
            $table->timestamps();

            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_vehicle');
    }
};
