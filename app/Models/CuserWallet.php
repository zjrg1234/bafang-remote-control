<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class CuserWallet extends Model
{
    use HasFactory;
    protected $table = 'cuser_wallet';

    protected $fillable = [
        'uid',
        'balance',
        'phone_number',
        'energy',
        'type',
    ];
    static function getBalance($uid,$special_area): array {
        $wallet = self::where('uid',$uid)->where('type',$special_area)->first();
        if (!$wallet) {

            $lock_key = 'huazhi:wallet:' . $uid.':create:'.$special_area;
            $ret = Redis::set($lock_key, '1','ex','5','nx');
            $exists = CuserWalletLog::where('uid',$uid)->where('type',6)->first();
            if($exists) {
                $energy = 0;
            }else{
                $energy = 4;
                CuserWalletLog::create(['uid'=>$uid,'type'=>6,'amount'=>4,'type_name'=>'注册赠送能量']);
            }
            if($ret) {
                self::create([
                    'uid'   => $uid,
                    'balance'   => 0,
                    'energy'    => $energy,
                    'type'   => $special_area,
                ]);
            }

            return [
                'balance'   => '0',
                'energy'    => $energy,
            ];
        }

        return [
            'balance'   => $wallet->balance,
            'energy'    => $wallet->energy,
        ];
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
//    static function getBalance($uid, $currency): array {
//        $wallet = self::where(['uid' => $uid, 'currency' => $currency])->first();
//        $app_platform = getDatasourcePlatform();
//        if (!$wallet) {
//
//            $lock_key = 'yuanbao:wallet:' . $uid.':create:'.$currency;
//            $ret = Redis::set($lock_key, '1','ex','5','nx');
//            if($ret) {
//                self::create([
//                    'uid'   => $uid,
//                    'balance'   => 0,
//                    'currency'  => $currency,
//                    'freeze_balance'    => 0,
//                    'app_platform'  =>$app_platform,
//                ]);
//            }
//
//            return [
//                'balance'   => '0.000000',
//                'freeze_balance'    => '0.000000',
//            ];
//        }
//
//        return [
//            'balance'   => $wallet->balance,
//            'freeze_balance'    => $wallet->freeze_balance,
//        ];
//    }
}
