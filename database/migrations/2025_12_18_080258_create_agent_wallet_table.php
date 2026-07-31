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
        Schema::create('agent_wallet', function (Blueprint $table) {
            $table->id();
            $table->integer('agent_id')->default(0)->comment('代理商id');
            $table->integer('balance')->default(0)->comment('余额');
            $table->index('agent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_wallet');
    }
};
