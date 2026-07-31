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
        Schema::create('agent_venue', function (Blueprint $table) {
            $table->id();
            $table->integer('agent_id')->nullable()->comment('代理商id');
            $table->string('venue_name')->default('')->comment('场地名称');
            $table->string('agent_name')->default('')->comment('代理商名称');
            $table->text('venue_image')->nullable()->comment('场地图片最多三张');
            $table->string('venue_introduction')->default('')->comment('场地介绍');
            $table->integer('start_time')->default(0)->comment('开始营业时间');
            $table->integer('end_time')->default(0)->comment('结束营业时间');
            $table->integer('vehicle_id')->default(0)->comment('场地类型 预设：1遥控车 2遥控船 3工程车');
            $table->integer('deposit')->default(0)->comment('押金');
            $table->integer('vehicle_count')->default(0)->comment('车辆数量');
            $table->integer('online_vehicle')->default(0)->comment('在线车辆数');
            $table->integer('support_status')->default(0)->comment('营业状态 0待定 1营业中');
            $table->integer('sorting')->default(0)->comment('排序');
            $table->string('provinces')->default('')->comment('省份');
            $table->string('labels')->default('')->comment('场地标签逗号分隔');
            $table->string('city')->default('')->comment('城市');
            $table->string('area')->default('')->comment('区');
            $table->text('venue_config')->nullable()->comment('计费方式');
            $table->index('agent_id');
            $table->index('start_time');
            $table->index('vehicle_id');
            $table->index('end_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_venue');
    }
};
