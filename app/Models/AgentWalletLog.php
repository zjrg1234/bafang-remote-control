<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $agent_id
 * @property integer $type
 * @property string $type_name
 * @property integer $amount
 * @property integer $balance
 * @property string $make_order_no
 * @property string $venue
 * @property string $make_user_name
 * @property integer $make_phone
 * @property integer $time
 * @property string $first_handling_fee
 * @property string $company_handling_fee
 * @property integer $first_amount
 * @property integer $company_amount
 * @property string $created_at
 * @property string $updated_at
 */
class AgentWalletLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agent_wallet_log';

    /**
     * @var array
     */
    protected $fillable = ['agent_id', 'type', 'type_name', 'amount', 'balance', 'make_order_no', 'venue', 'user_name', 'phone', 'time', 'first_handling_fee', 'company_handling_fee', 'first_amount', 'company_amount', 'created_at', 'updated_at'];

    const TypeDeposit = 1, //收入
        TypeConsumption = 2  //支出

    ;
    static $typeNames=[
        self::TypeDeposit => '收入',
        self::TypeConsumption => '支出',


    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
