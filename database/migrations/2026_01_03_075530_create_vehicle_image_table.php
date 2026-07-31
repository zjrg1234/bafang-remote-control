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
        Schema::create('vehicle_image', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0)->comment('类型：1车辆默认图片 2遥控船 3挖机 4铲车 5娃娃机');
            $table->string('type_name')->default('')->comment('类型名称');
            $table->string('image')->default('')->comment('图片');
            $table->integer('status')->default(0)->comment('状态 0不启用 1启用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_image');
    }
};
