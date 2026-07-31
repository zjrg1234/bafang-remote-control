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
        Schema::create('protocol_manage', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0)->comment('type');
            $table->string('name')->default('')->comment('类型名称');
            $table->longText('content')->nullable()->comment('内容');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_manage');
    }
};
