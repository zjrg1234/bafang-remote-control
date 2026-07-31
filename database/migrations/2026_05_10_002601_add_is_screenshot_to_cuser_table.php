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
        Schema::table('cuser', function (Blueprint $table) {
            $table->integer('is_screenshot')->after('is_locked')->default(0)->comment('是否可截图 0否 1是');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuser', function (Blueprint $table) {
            //
        });
    }
};
