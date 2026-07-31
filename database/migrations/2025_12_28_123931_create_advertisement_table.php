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
        Schema::create('advertisement', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('')->comment('标题');
            $table->string('image')->default('')->comment('图片地址');
            $table->integer('status')->default(0)->comment('是否默认 0否 1是');
            $table->integer('type')->default(0)->comment('预留');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement');
    }
};
