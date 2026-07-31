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
        Schema::create('driving_record', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->default(0)->comment('用户id');
            $table->integer('agent_id')->default(0)->comment('用户id');
            $table->string('user_name')->default(0)->comment('用户名称');
            $table->integer('special_area')->default(0)->comment('专区type');
            $table->string('special_area_name')->default('')->comment('专区名称');
            $table->string('phone')->default(0)->comment('用户电话');
            $table->integer('vehicle_id')->default(0)->comment('车辆id');
            $table->string('vehicle_name')->default('')->comment('车辆名称');
            $table->integer('venue_id')->default(0)->comment('场地id');
            $table->string('venue_name')->default('')->comment('场地名称');
            $table->string('order_no')->default('')->comment('订单编号/预约号');
            $table->integer('payment_type')->default(0)->comment('支付类型 1电池 2能量');
            $table->integer('reservation_status')->default(0)->comment('预约状态 1已预约 2待使用 3使用中 4已完成 5已取消 ');
            $table->integer('payment_amount')->default(0)->comment('支付金额');
            $table->integer('start_time')->default(0)->comment('开始时间');
            $table->integer('end_time')->default(0)->comment('结束时间');
            $table->integer('order_time')->default(0)->comment('订单时间');
            $table->integer('billing_method')->default(0)->comment('计费方式 0按时间 1按次');
            $table->integer('appeal_status')->default(0)->comment('申诉状态 0未申请 1待处理 2已处理');
            $table->string('billing_rules')->default('')->comment('计费规则');
            $table->index('uid');
            $table->index('agent_id');
            $table->index('order_no');
            $table->index('vehicle_id');
            $table->index('venue_id');
            $table->index('special_area');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driving_record');
    }
};
