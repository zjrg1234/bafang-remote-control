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
        Schema::create('banner', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->comment('名称');
            $table->string('image')->default('')->comment('图片链接');
            $table->string('url')->default('')->comment('跳转链接');
            $table->string('type')->default('')->comment('预留字段');
            $table->integer('status')->default(0)->comment('是否开启（展示不展示） 0否1 是');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner');
    }
};
