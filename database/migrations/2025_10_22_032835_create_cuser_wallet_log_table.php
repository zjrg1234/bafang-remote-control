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
        Schema::create('cuser_wallet_log', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->nullable()->comment('用户id');
            $table->integer('type')->default(0)->comment('类型：预留具体见model内定义');
            $table->integer('special_area')->default(0)->comment('专区type');
            $table->string('special_area_name')->default('')->comment('专区名称');
            $table->string('type_name')->default('')->comment('类型名称');
            $table->integer('amount')->default(0)->comment('金额');
            $table->integer('balance')->default(0)->comment('余额');
            $table->string('make_order_no')->default('')->comment('预约号');
            $table->string('user_name')->default('')->comment('用户名');
            $table->string('phone')->default('')->comment('手机号');
            $table->string('venue')->default('')->comment('场地');
            $table->string('operator_name')->default('')->comment('操作人姓名');
            $table->string('operator_account')->default('')->comment('操作人账号');
            $table->integer('time')->default(0)->comment('时间戳');
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
        Schema::dropIfExists('cuser_wallet_log');
    }
};
