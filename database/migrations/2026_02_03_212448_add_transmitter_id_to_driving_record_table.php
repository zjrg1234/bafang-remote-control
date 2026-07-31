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
        Schema::table('driving_record', function (Blueprint $table) {
            $table->string('transmitter_id')->default('')->comment('发射机');
            $table->string('receiver_id')->default('')->comment('接收机');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driving_record', function (Blueprint $table) {
            //
        });
    }
};
