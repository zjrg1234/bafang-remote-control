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
        Schema::create('agent_withdraw_log', function (Blueprint $table) {
            $table->id();
            $table->integer('agent_id')->default(0)->comment('代理商id');
            $table->string('agent_name')->default('')->comment('代理商名称');
            $table->integer('withdraw_type')->default(0)->comment('提现方式');
            $table->integer('withdraw_amount')->default(0)->comment('提现金额');
            $table->integer('balance')->default(0)->comment('提现后余额');
            $table->integer('status')->default(0)->comment('0:待审核 1审核通过 2未通过');
            $table->integer('enrolment_time')->default(0)->comment('申请时间');
            $table->integer('audit_time')->default(0)->comment('审核时间');
            $table->string('withdraw_name')->default('')->comment('收款人姓名');
            $table->string('withdraw_account')->default('')->comment('收款账号');
            $table->string('bank')->default('')->comment('银行');
            $table->string('bank_number')->default('')->comment('银行卡号');
            $table->index('agent_id');
            $table->index('agent_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_withdraw_log');
    }
};
