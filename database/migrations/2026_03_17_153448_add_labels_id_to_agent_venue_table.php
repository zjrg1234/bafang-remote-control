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
        Schema::table('agent_venue', function (Blueprint $table) {
            $table->integer('label_id')->default(1)->comment('1:遥控车 2越野车 3工程车');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_venue', function (Blueprint $table) {
            //
        });
    }
};
