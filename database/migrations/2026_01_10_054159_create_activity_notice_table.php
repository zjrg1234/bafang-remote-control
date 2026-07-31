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
        Schema::create('activity_notice', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0)->comment('公告所属类型 :1平台公告 2专区公告');
            $table->integer('special_area')->default(0)->comment('专区type');
            $table->string('special_area_name')->default('')->comment('专区名称');
            $table->string('activity_title')->default('')->comment('活动标题');
            $table->string('activity_image')->default('')->comment('活动图片');
            $table->integer('is_index')->default(0)->comment('是否首页显示 :0否 1是');
            $table->string('index_image')->default('')->comment('首页图片');
            $table->integer('is_discover')->default(0)->comment('是否发现页显示 :0否 1是');
            $table->string('discover_image')->default('')->comment('发现页图片');
            $table->text('content')->nullable()->comment('内容');
            $table->integer('activity_type')->default(0)->comment('活动公告指向类型 :1活动公告 2排行榜');
            $table->integer('status')->default(0)->comment('状态 0不启用 1启用');
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
        Schema::dropIfExists('activity_notice');
    }
};
