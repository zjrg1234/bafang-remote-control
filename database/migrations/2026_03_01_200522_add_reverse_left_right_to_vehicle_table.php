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
        Schema::table('vehicle', function (Blueprint $table) {
            $table->integer('reverse_left_right')->default(0)->after('app_transmitter_id')->comment('方向 - 反向操作是否开启 0否1是');
            $table->integer('reverse_up_down')->default(0)->after('reverse_left_right')->comment('进退 - 反向操作是否开启 0否1是');
            $table->integer('reverse_rotation')->default(0)->after('reverse_up_down')->comment('旋转 - 反向操作是否开启 0否1是');
            $table->integer('change_ui_control')->default(0)->after('reverse_rotation')->comment('是否修改控制UI状态 0否1是');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle', function (Blueprint $table) {
            //
        });
    }
};
