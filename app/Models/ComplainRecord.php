<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $uid
 * @property string $order_no
 * @property string $user_name
 * @property string $phone
 * @property integer $venue_id
 * @property string $venue_name
 * @property integer $vehicle_id
 * @property string $vehicle_name
 * @property integer $amount
 * @property integer $reservation_status
 * @property integer $billing_method
 * @property integer $appeal_status
 * @property integer $time
 * @property integer $refund_amount
 * @property integer $refund_type
 * @property string $refund_cause
 * @property string $platform_reply
 * @property string $created_at
 * @property string $updated_at
 */
class ComplainRecord extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'complain_record';

    /**
     * @var array
     */
    protected $fillable = ['uid', 'order_no', 'user_name', 'phone', 'venue_id', 'venue_name', 'vehicle_id', 'vehicle_name', 'amount', 'reservation_status', 'billing_method', 'appeal_status', 'time', 'refund_amount', 'refund_type', 'refund_cause', 'platform_reply','payment_type', 'created_at', 'updated_at'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
