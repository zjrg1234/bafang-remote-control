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
        Schema::create('cuser_agent', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->nullable()->comment('用户id');
            $table->string('agent_name')->default('')->comment('代理商名称');
            $table->string('password')->default('')->comment('登陆密码');
            $table->integer('level')->nullable()->comment('等级');
            $table->integer('zone_id')->default(0)->comment('专区id1级代理商默认0 2级代理商需绑定1级代理商专区id');
            $table->integer('superior_agent_id')->default(0)->comment('上级代理商id 0默认华智');
            $table->integer('withdrawal_amount')->default(0)->comment('提现金额');
            $table->string('first_handling_fee')->default('')->comment('一级代理商手续费');
            $table->string('company_handling_fee')->default('')->comment('公司手续费');
            $table->string('phone_number')->default('')->comment('手机号');
            $table->integer('venue_quantity')->default(0)->comment('场地数量');
            $table->integer('create_site_quantity')->default(3)->comment('可创建场地总数');
            $table->integer('is_support')->default(0)->comment('是否自营 0否 1是');
            $table->string('head_shot')->default('')->comment('头像');
            $table->string('provinces')->default('')->comment('省份');
            $table->string('city')->default('')->comment('城市');
            $table->integer('register_time')->default(0)->comment('注册时间戳');
            $table->integer('review_status')->default(0)->comment('审核状态 0待审核 1通过');
            $table->integer('support_status')->default(0)->comment('营业状态 0待定 1营业中');
            $table->integer('is_cancel')->default(0)->comment('是否注销：0否 1是');
            $table->integer('is_frozen')->default(0)->comment('是否冻结：0否 1是');
            $table->integer('sorting')->default(0)->comment('排序');
            $table->integer('yesterday_turnover')->default(0)->comment('昨日营业额');
            $table->index('uid');
            $table->index('agent_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuser_agent');
    }
};
