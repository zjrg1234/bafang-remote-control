<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $agent_id
 * @property string $agent_name
 * @property integer $withdraw_type
 * @property integer $withdraw_amount
 * @property integer $balance
 * @property integer $status
 * @property integer $enrolment_time
 * @property integer $audit_time
 * @property string $withdraw_name
 * @property string $withdraw_account
 * @property string $bank
 * @property string $bank_number
 * @property string $created_at
 * @property string $updated_at
 */
class AgentWithdrawLog extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'agent_withdraw_log';

    /**
     * @var array
     */
    protected $fillable = ['agent_id', 'agent_name', 'withdraw_type', 'withdraw_amount', 'balance', 'status', 'enrolment_time', 'audit_time', 'withdraw_name', 'withdraw_account', 'bank', 'bank_number', 'created_at', 'updated_at'];
}
