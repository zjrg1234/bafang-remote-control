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
        Schema::create('war_zone', function (Blueprint $table) {
            $table->id();
            $table->integer('agent_id')->default(0)->comment('1级代理商id');
            $table->string('name')->default('')->comment('专区名称');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('war_zone');
    }
};
