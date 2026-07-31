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
        Schema::create('cuser_wallet', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->default(0)->comment('用户id');
            $table->integer('balance')->default(0)->comment('用户电池');
            $table->integer('energy')->default(0)->comment('用户能量');
            $table->integer('type')->default( 0)->comment('预留 专区id');
            $table->index('uid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuser_wallet');
    }
};
