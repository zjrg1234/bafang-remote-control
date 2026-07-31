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
        Schema::create('admin_user', function (Blueprint $table) {
            $table->id();
            $table->string('username')->default('')->comment('登陆用户名');
            $table->string('password')->default('')->comment('密码');
            $table->integer('type')->default(0)->comment('状态： 0停用 1启用');
            $table->string('secret')->default('')->comment('谷歌二次验证secret');
            $table->string('name')->default('')->comment('名称');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user');
    }
};
