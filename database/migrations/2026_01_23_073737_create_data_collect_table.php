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
        Schema::create('data_collect', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_sale',15,2)->default(0)->comment('总销售额');
            $table->integer('total_make')->default(0)->comment('总预约数');
            $table->integer('total_payment')->default(0)->comment('总支付数');
            $table->decimal('total_refund',15,2)->default(0)->comment('总退款');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_collect');
    }
};
