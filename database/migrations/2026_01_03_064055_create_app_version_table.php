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
        Schema::create('app_version', function (Blueprint $table) {
            $table->id();
            $table->string('version_mark')->default('')->comment('版本号');
            $table->string('version_coding')->default('')->comment('版本编码');
            $table->integer('type')->default(0)->comment('1苹果 2安卓');
            $table->text('update_content')->nullable()->comment('内容');
            $table->integer('is_change_special')->default(0)->comment('0否1是');
            $table->integer('forced_updating')->default(0)->comment('是否强制更新0否1是');
            $table->integer('status')->default(0)->comment('0不启用1启用');
            $table->string('app_url')->default('')->comment('app链接');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_version');
    }
};
