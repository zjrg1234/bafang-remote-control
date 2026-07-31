<?php

namespace App\Http\Service;

use App\Models\Cuser;
use App\Models\CuserEnergyLog;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WalletService
{

    /**
     * 安全减 / (amount 为正数)
     * [
     * 'uid' => , 'platform_id' => ,'order_no' => '', 'platform_order_no' => ,'type'   => , 'amount' => , 'currency' => '', ,'msg' => '',
     * ];
     * 不能减为负数 , 需要减为负数请用 ->add
     * 需要在调用之前 获得 锁和 DB::beginTransaction() ,  结束活异常之后
     * @param $data
     */
    static function safeAdjust($data)
    {
        $balance = CuserWallet::getBalance($data['uid'],$data['special_area']);

        $updateQuery = CuserWallet::where(['uid' => $data['uid']])->where('type',$data['special_area']);
        $affected = $updateQuery->update(['balance' => DB::raw("balance+{$data['amount']}")]);
        if ($affected != 1) {
            throw new InsufficientBalanceException("want {$data['amount']}, has {$balance['balance']}");
        }

        $userInfo = Cuser::find($data['uid']);
        if (!$userInfo) {
            return false;
        }
        Log::info("safeAdjust : " . json_encode($data, 320));
        if($data['amount'] < 0){
            $data['amount'] = $data['amount'] * -1;
           $balance['balance'] =  $balance['balance'] - $data['amount'];
        }else{
            $balance['balance'] = $balance['balance'] + $data['amount'];
        }

        $logItem = CuserWalletLog::create([
            'uid' => $data['uid'],
            'make_order_no' => $data['make_order_no'],
            'type' => $data['type'],
            'type_name' => $data['type_name'],
            'amount' => $data['amount'],
            'venue' => $data['venue'],
//            'before_balance'    => $balance['balance'],
            'balance' => $balance['balance'],
            'time' => $data['time'] ?? time(), // 如果有就用，如果没有就用现在时间
            'user_name'=>$userInfo['username'] ?? '',
            'phone'=>$userInfo['phone_number'] ?? '',
            'operator_name'=> $userInfo['operator_name'] ?? '',
            'operator_account'=> $userInfo['operator_account'] ?? '',
            'special_area' => $data['special_area'],
//            'remark'            => $data['remark']??'',
        ]);

//        if(in_array($data['type'],wallet::$bonusTypes)){
//            $userArr =$userInfo->toArray();
//            KafkaMessageService::bonus($userArr,$logItem);
//        }
//
//        //全部发送
//        if(1 || in_array($data['type'],wallet::$WalletChangeTypes)){
//            $userArr =$userInfo->toArray();
//            KafkaMessageService::walletChange($userArr,$logItem);
//        }

        return $logItem->id;
    }

    /**
     * 安全减 / (amount 为正数)
     * [
     * 'uid' => , 'platform_id' => ,'order_no' => '', 'platform_order_no' => ,'type'   => , 'amount' => , 'currency' => '', ,'msg' => '',
     * ];
     * 不能减为负数 , 需要减为负数请用 ->add
     * 需要在调用之前 获得 锁和 DB::beginTransaction() ,  结束活异常之后
     * @param $data
     */
    static function safeAdjustEnergy($data)
    {
        $balance = CuserWallet::getBalance($data['uid'],$data['special_area']);

        $updateQuery = CuserWallet::where(['uid' => $data['uid']])->where('type',$data['special_area']);
        $affected = $updateQuery->update(['energy' => DB::raw("energy+{$data['amount']}")]);
        if ($affected != 1) {
            throw new InsufficientBalanceException("want {$data['amount']}, has {$balance['energy']}");
        }

        $userInfo = Cuser::find($data['uid']);
        if (!$userInfo) {
            return false;
        }
        Log::info("safeAdjust : " . json_encode($data, 320));
        if($data['amount'] < 0){
            $data['amount'] = $data['amount'] * -1;
            $balance['energy'] =  $balance['energy'] - $data['amount'];
        }else{
            $balance['energy'] = $balance['energy'] + $data['amount'];
        }

        $logItem = CuserEnergyLog::create([
            'uid' => $data['uid'],
            'make_order_no' => $data['make_order_no'] ?? '',
            'type' => $data['type'],
            'type_name' => $data['type_name'],
            'venue' => $data['venue'] ?? '',
            'energy' => $data['amount'],
//            'before_balance'    => $balance['balance'],
            'surplus_energy' => $balance['energy'],
            'activity_id' => $data['activity_id'] ?? '',
            'time' => $data['time'] ?? time(), // 如果有就用，如果没有就用现在时间
            'user_name'=>$userInfo['username'] ?? '',
            'operator_name'=> $userInfo['operator_name'] ?? '',
            'operator_account'=> $userInfo['operator_account'] ?? '',
            'special_area' => $data['special_area'] ?? 0,
            'phone'=>$userInfo['phone_number'] ?? '',
//            'remark'            => $data['remark']??'',
        ]);

//        if(in_array($data['type'],wallet::$bonusTypes)){
//            $userArr =$userInfo->toArray();
//            KafkaMessageService::bonus($userArr,$logItem);
//        }
//
//        //全部发送
//        if(1 || in_array($data['type'],wallet::$WalletChangeTypes)){
//            $userArr =$userInfo->toArray();
//            KafkaMessageService::walletChange($userArr,$logItem);
//        }

        return $logItem->id;

    }
}
