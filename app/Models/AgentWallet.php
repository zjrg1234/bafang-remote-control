<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

/**
 * @property integer $id
 * @property integer $agent_id
 * @property integer $balance
 * @property string $created_at
 * @property string $updated_at
 */
class AgentWallet extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agent_wallet';

    /**
     * @var array
     */
    protected $fillable = ['agent_id', 'balance', 'created_at', 'updated_at'];

    static function getBalance($agent_id): array {
        $wallet = self::where('agent_id',$agent_id)->first();
        if (!$wallet) {

            $lock_key = 'huazhi:agent:wallet:' . $agent_id.':create:';
            $ret = Redis::set($lock_key, '1','ex','5','nx');
            if($ret) {
                self::create([
                    'agent_id'   => $agent_id,
                    'balance'   => 0,
                ]);
            }

            return [
                'balance'   => '0',
            ];
        }

        return [
            'balance'   => $wallet->balance,
        ];
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
