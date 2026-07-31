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
        Schema::table('complain_record', function (Blueprint $table) {
            $table->integer('payment_type')->default(0)->after('billing_method')->comment('支付类型 1电池 2能量');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complain_record', function (Blueprint $table) {
            //
        });
    }
};
