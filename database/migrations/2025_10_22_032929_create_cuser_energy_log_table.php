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
        Schema::create('cuser_energy_log', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->nullable()->comment('用户id');
            $table->integer('type')->default(0)->comment('类型：1:注册赠送能量 2:充值优惠赠送');
            $table->string('type_name')->default('')->comment('类型名称');
            $table->integer('special_area')->default(0)->comment('专区type');
            $table->string('special_area_name')->default('')->comment('专区名称');
            $table->integer('energy')->default(0)->comment('金额');
            $table->integer('surplus_energy')->default(0)->comment('余额');
            $table->string('make_order_no')->default('')->comment('预约号');
            $table->string('venue')->default('')->comment('场地');
            $table->integer('recharge_amount')->default(0)->comment('充值金额');
            $table->integer('time')->default(0)->comment('时间戳');
            $table->string('activity_id')->default('')->comment('活动id');
            $table->string('user_name')->default('')->comment('用户名');
            $table->string('phone')->default('')->comment('手机号');
            $table->string('operator_name')->default('')->comment('操作人姓名');
            $table->string('operator_account')->default('')->comment('操作人账号');
            $table->index('make_order_no');
            $table->index('uid');
            $table->index('time');
            $table->index('user_name');
            $table->index('phone');
            $table->index('operator_name');
            $table->index('operator_account');
            $table->index('special_area');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuser_energy_log');
    }
};
