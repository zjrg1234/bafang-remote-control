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
        Schema::create('deposit_log', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->default('')->comment('订单号');
            $table->string('third_order_no')->default('')->comment('三方订单号（微信、支付宝）');
            $table->integer('uid')->default(0)->index('uid')->comment('用户ID');
            $table->string('user_name')->default('')->comment('用户名称');
            $table->integer('special_area')->default(0)->comment('专区id');
            $table->string('special_area_name')->default('')->comment('专区名称');
            $table->string('phone_number')->default('')->comment('电话');
            $table->string('activity_id')->default('')->comment('活动id');
            $table->integer('amount')->default(0)->comment('充值金额');
            $table->integer('time')->default(0)->comment('添加时间');
            $table->integer('type')->default(0)->comment('0 未充值 1已成功 2退款');
            $table->integer('finish_time')->default(0)->comment('完成时间');
            $table->integer('sendMoney')->default(0)->comment('赠送的能量');
            $table->integer('pay_id')->default(0)->comment('支付ID');
            $table->string('energy_id')->default('')->comment('能量记录id');
            $table->index('order_no');
            $table->index('third_order_no');
            $table->index('special_area');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_log');
    }
};
