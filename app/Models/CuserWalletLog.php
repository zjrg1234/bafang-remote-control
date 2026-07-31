<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuserWalletLog extends Model
{
    protected $table = 'cuser_wallet_log';

    use HasFactory;

    protected $fillable = [
        'uid',
        'type',
        'type_name',
        'amount',
        'balance',
        'make_order_no',
        'venue',
        'user_name',
        'phone',
        'operator_name',
        'operator_account',
        'special_area',
        'special_area_name',
        'make_phone',
        'time',
    ];
    const TypeDeposit = 1, //充值
          TypeConsumption = 2,  //驾驶
          TypeReturn = 3, //退还
          TypeChange = 4,//管理员修改余额
          TypePlatformRefund = 5; //平台退款

    static $typeNames = [
        self::TypeDeposit => '充值',
        self::TypeConsumption => '驾驶',
        self::TypeReturn => '退还',
        self::TypeChange => '管理员修改余额',
        self::TypePlatformRefund => '平台退款',

    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
