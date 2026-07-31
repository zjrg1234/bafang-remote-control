<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuserEnergyLog extends Model
{
    protected $table = 'cuser_energy_log';

    use HasFactory;
    protected $fillable = [
        'uid',
        'type',
        'type_name',
        'energy',
        'surplus_energy',
        'make_order_no',
        'venue',
        'recharge_amount',
        'activity_id',
        'user_name',
        'phone',
        'operator_name',
        'operator_account',
        'special_area',
        'special_area_name',
        'time',
    ];
    const TypeDeposit = 1, //充值
        TypeConsumption = 2,  //驾驶消耗
        TypeReturn = 3, //退还
        TypeChange = 4,
        TypePlatformRefund = 5
        ;
    static $typeNames=[
        self::TypeDeposit => '充值',
        self::TypeConsumption => '驾驶扣款',
        self::TypeReturn => '退还',
        self::TypeChange => '管理员修改能量',
        self::TypePlatformRefund => '平台退款',

    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
