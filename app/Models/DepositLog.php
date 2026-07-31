<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $order_no
 * @property string $third_order_no
 * @property integer $uid
 * @property string $user_name
 * @property integer $special_area
 * @property string $special_area_name
 * @property string $phone_number
 * @property string $activity_id
 * @property integer $amount
 * @property integer $time
 * @property integer $type
 * @property integer $finish_time
 * @property integer $sendMoney
 * @property integer $pay_id
 * @property string $energy_id
 * @property string $created_at
 * @property string $updated_at
 */
class DepositLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deposit_log';

    /**
     * @var array
     */
    protected $fillable = ['order_no', 'third_order_no', 'uid', 'user_name', 'special_area', 'special_area_name', 'phone_number', 'activity_id', 'amount', 'time', 'type', 'finish_time', 'sendMoney', 'pay_id', 'energy_id','pay_type', 'created_at', 'updated_at'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
