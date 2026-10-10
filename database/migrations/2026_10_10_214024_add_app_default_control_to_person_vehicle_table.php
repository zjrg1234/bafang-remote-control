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
        Schema::table('person_vehicle', function (Blueprint $table) {
            $table->integer('default_control')->default(1)->comment('1app操控 2遥控器');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_vehicle', function (Blueprint $table) {
            //
        });
    }
};
