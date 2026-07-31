<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $uid
 * @property string $user_name
 * @property string $phone
 * @property integer $vehicle_id
 * @property string $vehicle_name
 * @property integer $venue_id
 * @property string $venue_name
 * @property string $order_no
 * @property integer $payment_type
 * @property integer $reservation_status
 * @property integer $payment_amount
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $order_time
 * @property integer $billing_method
 * @property integer $appeal_status
 * @property string $billing_rules
 * @property string $created_at
 * @property string $updated_at
 */
class DrivingRecord extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'driving_record';

    /**
     * @var array
     */
    protected $fillable = ['uid', 'agent_id','user_name', 'phone','head_shot', 'vehicle_id', 'vehicle_name', 'venue_id', 'venue_name', 'order_no', 'payment_type', 'reservation_status', 'payment_amount', 'start_time', 'end_time', 'order_time', 'billing_method', 'appeal_status', 'billing_rules','special_area',
        'special_area_name','transmitter_id','receiver_id', 'created_at', 'updated_at'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
