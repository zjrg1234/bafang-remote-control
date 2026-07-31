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
        Schema::create('receiver_transmitter_config', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id')->default(0)->comment('车辆id');
            $table->string('receiver_id')->default('')->comment('接收机id');
            $table->string('transmitter_id')->default('')->comment('发射机id');
            $table->string('receiver_host_port')->default('')->comment('接收机域名+端口');
            $table->string('transmitter_host_port')->default('')->comment('发射机域名+端口');
            $table->index('transmitter_id');
            $table->index('receiver_id');
            $table->index('vehicle_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiver_transmitter_config');
    }
};
