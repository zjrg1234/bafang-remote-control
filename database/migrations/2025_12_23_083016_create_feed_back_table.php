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
        Schema::create('feed_back', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->default(0)->comment('用户uid');
            $table->integer('agents_id')->default(0)->comment('代理商id');
            $table->string('user_name')->default('')->comment('用户名称');
            $table->string('phone')->default('')->comment('用户手机');
            $table->text('Content')->nullable()->comment('意见内容');
            $table->string('image')->default('')->comment('图片');
            $table->integer('type')->default(0)->comment('0待回复 1已回复');
            $table->string('time')->default(0)->comment('时间戳');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_back');
    }
};
