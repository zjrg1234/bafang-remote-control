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
        Schema::create('cuser', function (Blueprint $table) {
            $table->id();
            $table->string('username')->default('')->comment('用户姓名');
            $table->string('nick_name')->default('')->comment('昵称');
            $table->string('password')->default('')->comment('登陆密码');
            $table->string('phone_number')->default('')->comment('手机号');
            $table->integer('special_area')->default(0)->comment('专区type');
            $table->string('special_area_name')->default('')->comment('专区名称');
            $table->string('head_shot')->default('')->comment('头像');
            $table->integer('is_real_name')->default(0)->comment('是否实名认证：0否1是');
            $table->string('real_name')->default('')->comment('真是姓名');
            $table->integer('is_cancel')->default(0)->comment('是否注销：0否 1是');
            $table->integer('register_time')->default(0)->comment('注册时间戳');
            $table->string('login_ip')->default('')->comment('登陆ip');
            $table->integer('last_online_time')->default(0)->comment('最后操作时间');
            $table->string('session_key')->default('')->comment('登陆token');
            $table->integer('is_locked')->default(0)->comment('是否封号：0否 1是');
            $table->index('username');
            $table->index('phone_number');
            $table->index('special_area');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuser');
    }
};
