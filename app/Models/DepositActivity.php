<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $activity_id
 * @property integer $payment_amount
 * @property integer $send_energy
 * @property integer $num
 * @property integer $type
 * @property integer $sort
 * @property string $remark
 * @property string $created_at
 * @property string $updated_at
 */
class DepositActivity extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'deposit_activity';

    /**
     * @var array
     */
    protected $fillable = ['activity_id', 'payment_amount', 'send_energy', 'num', 'type', 'sort', 'remark', 'created_at', 'updated_at'];
}
