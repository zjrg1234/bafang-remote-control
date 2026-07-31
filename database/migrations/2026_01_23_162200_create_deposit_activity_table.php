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
        Schema::create('deposit_activity', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->default('')->comment('活动id');
            $table->integer('payment_amount')->default(0)->comment('充值金额');
            $table->integer('send_energy')->default(0)->comment('赠送能量');
            $table->integer('num')->default(0)->comment('限制次数');
            $table->integer('type')->default(0)->comment('0禁用 1启用');
            $table->integer('sort')->default(0)->comment('排序号');
            $table->string('remark')->default('')->comment('备注');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_activity');
    }
};
