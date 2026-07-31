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
        Schema::create('complain_record', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->default(0)->comment('用户id');
            $table->string('order_no')->default('')->comment('预约号');
            $table->string('user_name')->default('')->comment('用户名称');
            $table->string('image')->default('')->comment('图片');
            $table->string('phone')->default('')->comment('手机号');
            $table->integer('venue_id')->default(0)->comment('场地id');
            $table->string('venue_name')->default('')->comment('场地名称');
            $table->integer('vehicle_id')->default(0)->comment('车辆id');
            $table->string('vehicle_name')->default('')->comment('车辆名称');
            $table->integer('amount')->default(0)->comment('支付金额');
            $table->integer('reservation_status')->default(0)->comment('预约状态 1已预约 2待使用 3使用中 4已完成 5已取消 ');
            $table->integer('billing_method')->default(0)->comment('计费方式 0按时间 1按次');
            $table->integer('appeal_status')->default(0)->comment('申诉状态 0未申请 1待处理 2已处理');
            $table->integer('time')->default(0)->comment('申请时间');
            $table->integer('refund_amount')->default(0)->comment('退款金额');
            $table->integer('refund_type')->default(0)->comment('退款状态 0未退款 1已添加');
            $table->text('refund_cause')->nullable()->comment('退款原因');
            $table->text('platform_reply')->nullable()->comment('平台回复');
            $table->index('uid');
            $table->index('order_no');
            $table->index('user_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complain_record');
    }
};
