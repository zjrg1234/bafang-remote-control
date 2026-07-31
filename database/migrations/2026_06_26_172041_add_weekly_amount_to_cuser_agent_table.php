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
        Schema::table('cuser_agent', function (Blueprint $table) {
            $table->integer('weekly_amount')->default(0)->comment('近7天总业绩(离线统计)')->after('sorting');
            $table->index('weekly_amount', 'idx_weekly_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuser_agent', function (Blueprint $table) {
            //
        });
    }
};
