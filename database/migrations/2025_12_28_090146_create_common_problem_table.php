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
        Schema::create('common_problem', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->comment('问题名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->integer('status')->default(0)->comment('是否启用 0否 1是');
            $table->longText('detail')->nullable()->comment('内容');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_problem');
    }
};
