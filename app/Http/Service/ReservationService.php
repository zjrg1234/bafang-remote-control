<?php

namespace App\Http\Service;


use App\Models\AgentWallet;
use App\Models\AgentWalletLog;
use App\Models\ComplainRecord;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserEnergyLog;
use App\Models\CuserWalletLog;
use App\Models\DrivingRecord;
use App\Models\ReponseData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ReservationService{


    public function reservationRecord($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'phone'         => $request['phone'] ?? null,
            'user_name'            => $request['user_name'] ?? null,
            'order_no'            => $request['order_no'] ?? null,
            'reservation_status'         => $request['reservation_status'] ?? null,
            'billing_method'  => $request['billing_method'] ?? null,
            'venue_id'    => $request['venue_id'] ?? null,
            'appeal_status'         => $request['appeal_status'] ?? null,
        ];
        $query = DrivingRecord::select(
            'id',
            'user_name',
            'order_no',
            'phone',
            'venue_id',
            'venue_name',
            'vehicle_id',
            'vehicle_name',
            'payment_type',
            'reservation_status',
            'payment_amount',
            'start_time',
            'end_time',
            'order_time',
            'billing_method',
            'appeal_status',
            'billing_rules',
            'special_area',
            'special_area_name');

        if(isset($query_params['phone'])){
            $query->where('phone',$query_params['phone']);
        }

        if(isset($query_params['user_name'])){

            $query->where('user_name','like', '%'.$query_params['user_name'].'%');
        }

        if(isset($query_params['order_no'])){
            $query->where('order_no',$query_params['order_no']);
        }

        if(isset($query_params['reservation_status'])){
            $query->where('reservation_status',$query_params['reservation_status']);
        }

        if(isset($query_params['appeal_status'])){
            $query->where('appeal_status',$query_params['appeal_status']);
        }

        if(isset($query_params['billing_method'])){
            $query->where('billing_method',$query_params['billing_method']);
        }
        if(isset($query_params['venue_id'])){
            $query->where('venue_id',$query_params['venue_id']);
        }
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        foreach ($rows as $value) {
            $value['start_time'] = date('Y-m-d H:i:s',$value['start_time']);
            $value['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
            $value['order_time'] = date('Y-m-d H:i:s',$value['order_time']);
            $json = json_decode($value['billing_rules'],true);

            $value['billing_rules'] = $json['time'] . '分钟' . $json['battery'] .'电池或能量';
        }

        return ReponseData::reponsePaginationFormat($rows);
    }

    public function complaintRecord($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'user_name'            => $request['user_name'] ?? null,
            'order_no'            => $request['order_no'] ?? null,
            'reservation_status'         => $request['reservation_status'] ?? null,
            'billing_method'  => $request['billing_method'] ?? null,
            'venue_id'    => $request['venue_id'] ?? null,
            'appeal_status'         => $request['appeal_status'] ?? null,
        ];

        $query = ComplainRecord::select(
            'id',
            'user_name',
            'order_no',
            'image',
            'phone',
            'venue_id',
            'venue_name',
            'vehicle_id',
            'vehicle_name',
            'reservation_status',
            'amount',
            'time',
            'billing_method',
            'appeal_status',
            'refund_amount',
            'refund_type',
            'refund_cause',
            'platform_reply',
            'payment_type',
        );

        $query = $query->where('amount','>',0);

        if(isset($query_params['user_name'])){
            $query->where('user_name','like', '%'.$query_params['user_name'].'%');
        }

        if(isset($query_params['order_no'])){
            $query->where('order_no',$query_params['order_no']);
        }

        if(isset($query_params['reservation_status'])){
            $query->where('reservation_status',$query_params['reservation_status']);
        }

        if(isset($query_params['appeal_status'])){
            $query->where('appeal_status',$query_params['appeal_status']);
        }

        if(isset($query_params['billing_method'])){
            $query->where('billing_method',$query_params['billing_method']);
        }
        if(isset($query_params['venue_id'])){
            $query->where('venue_id',$query_params['venue_id']);
        }

        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        $orderNos = array_column($rows->items(), 'order_no');
        $agent_ids = array_column($rows->items(), 'agent_id');
        $startTimeData = DrivingRecord::whereIn('order_no',$orderNos)->pluck('start_time','order_no');
        $endTimeData = DrivingRecord::whereIn('order_no',$orderNos)->pluck('end_time','order_no');
        $agentNames = CuserAgent::whereIn('id',$agent_ids)->pluck('agent_name','id');
        foreach ($rows as $value) {
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
            $value['refundable_amount'] = $value['amount'] - $value['refund_amount'];
            $value['start_time'] = date('Y-m-d H:i:s',$startTimeData[$value['order_no']]) ?? date('Y-m-d H:i:s',0);
            $value['end_time'] = date('Y-m-d H:i:s',$endTimeData[$value['order_no']]) ?? date('Y-m-d H:i:s',0);
            $value['agent_name'] = $agentNames[$value['agent_id']] ??  $value['agent_name'];
        }

        return ReponseData::reponsePaginationFormat($rows);

    }

    public function complaintUpdate($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? 2; // 2补能量 1补电池
        $refundAmount = $request['refund_amount'] ?? 0;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $complaint = ComplainRecord::where('id', $id)->first();
        if(!$complaint){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }

        if($refundAmount === 0){
            $update = [
                'appeal_status' => $request['appeal_status'] ?? $complaint['appeal_status'],
                'refund_cause' => $request['refund_cause'] ?? $complaint['refund_cause'],
                'platform_reply' => $request['platform_reply'] ?? $complaint['platform_reply'],
                'refund_amount' => $complaint['refund_amount'] + $request['refund_amount'],
                'refund_type'=>1,
            ];
            $complaint->update($update);
            $order = DrivingRecord::where('order_no', $complaint['order_no'])->first();
            if(!$order){
                return  ReponseData::reponseFormat(2000,'未找到该预约单子');
            }

            $order->update(['appeal_status' => 2]);
            return ReponseData::reponseFormat(200,'成功');


        }
//        if($complaint['appeal_status'] == 2){
//            return ReponseData::reponseFormat(200,'成功');
//        }
        if($request['refund_amount'] > $complaint['amount']) {
            return ReponseData::reponseFormat(2000,'可退金额超过可退金额');
        }
//        if($complaint['amount'] - $request['refund_amount'] <= 0){
//            return ReponseData::reponseFormat(2000,'可退金额不足');
//        }
        $update = [
            'appeal_status' => $request['appeal_status'] ?? $complaint['appeal_status'],
            'refund_cause' => $request['refund_cause'] ?? $complaint['refund_cause'],
            'platform_reply' => $request['platform_reply'] ?? $complaint['platform_reply'],
            'refund_amount' => $complaint['refund_amount'] + $request['refund_amount'],
            'refund_type'=>1,
        ];
        $complaint->update($update);
        $user = Cuser::where('id', $complaint['uid'])->first();
        $order = DrivingRecord::where('order_no', $complaint['order_no'])->first();
        if(!$order){
            return  ReponseData::reponseFormat(2000,'未找到该预约单子');
        }
        if($type == 1){
            WalletService::safeAdjust([
                'uid' => $user->id,
                'type' => CuserWalletLog::TypePlatformRefund,
                'type_name' => $request['refund_cause'],
                'make_order_no' => $complaint['order_no'],
                'amount' => $request['refund_amount'],
                'venue'  => $user->special_area_name,
                'special_area' => $user->special_area,
            ]);
            $agent_payment_amount =  $request['refund_amount'] * -1;
            $agentWallet = AgentWallet::getBalance($order['agent_id']);
            $balance = $agentWallet['balance'];
            $updateQuery = AgentWallet::where(['agent_id' => $order['agent_id']]);
            $affected = $updateQuery->update(['balance' => DB::raw("balance+{$agent_payment_amount}")]);
            if ($affected != 1) {
                Log::info("结束驾驶收入金额： {$agent_payment_amount}, 增加失败： {$agentWallet['balance']}");
            }
            $afterBalance = $balance - $request['refund_amount'];
            AgentWalletLog::create([
                'agent_id' => $order['agent_id'],
                'type' => 2,
                'type_name' => '用户申诉退款扣除',
                'amount' => $request['refund_amount'],
                'make_order_no' => $complaint['order_no'],
                'balance' => $afterBalance,
                'time' => time(),
            ]);
//            $complaint['payment_amount'] = $order['payment_amount'] - $update['refund_amount'];
        }

        if($type == 2){
            WalletService::safeAdjustEnergy([
                'uid' => $user->id,
                'type' => CuserEnergyLog::TypePlatformRefund,
                'type_name'=>'平台退款',
                'make_order_no' => orderNo('RF'),
                'amount' => $request['refund_amount'],
                'venue'  => $user->special_area_name,
                'special_area' => $user->special_area,
            ]);
        }

        $order->update(['appeal_status' => 2]);
        return ReponseData::reponseFormat(200,'成功');
    }

    public function refundRecord($request)
    {
        $id     = $request['id'] ?? null;
        $order_no = $request['order_no'] ?? null;
        $size = $request['size'] ?? 20;
        $page = $request['page'] ?? 1;
        if($id){
            $complaint = ComplainRecord::select('id','appeal_status','refund_cause','time','order_no')->where('id', $id)->first();
            if(!$complaint){
                return ReponseData::reponseFormat(2000,'未找到该数据');
            }
            $row = CuserWalletLog::select('id','amount','time','make_order_no','type')->where('make_order_no',$complaint['order_no'])->where('type',5)->get();
//            $rows = $row->orderBy("id", 'desc')->paginate($size, ['*'], 'page',$page);
            foreach($row as $value){
                $value['refund_cause'] = $complaint['refund_cause'];
                $value['time'] = date('Y-m-d H:i:s',$value['time']);
                $value['status'] = 1;
                unset($value['make_order_no']);
            }
            return ReponseData::reponseFormatList(200,'成功',$row);

        }

        if($order_no){
            $complaint = ComplainRecord::select('id','appeal_status','refund_cause','time','order_no')->where('order_no', $order_no)->first();
            if(!$complaint){
                return ReponseData::reponseFormat(2000,'未找到该数据');
            }
            $row = CuserWalletLog::select('id','amount','time','make_order_no','type')->where('make_order_no',$complaint['order_no'])->where('type',5);
            $rows = $row->orderBy("id", 'desc')->paginate($size, ['*'], 'page',$page);
            foreach($row as $value){
                $value['refund_cause'] = $complaint['refund_cause'];
                $value['time'] = date('Y-m-d H:i:s',$value['time']);
                $value['status'] = 1;
                unset($value['make_order_no']);
            }
//            foreach ($complaint as $value){
//                if($value['appeal_status'] == 2){
//                    $resp = [
//                        'id'    => $value['id'],
//                        'refund_cause' => $value['refund_cause'],
//                        'time' => date('Y-m-d H:i:s',$value['time']),
//                    ];
//                    $resp['status'] = 1;
//                    $data = $resp;
//                }
//            }
            return ReponseData::reponsePaginationFormat($rows);

        }
    }


    public function drivingRecord($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'phone'         => $request['phone'] ?? null,
            'user_name'            => $request['user_name'] ?? null,
            'order_no'            => $request['order_no'] ?? null,
            'vehicle_id'         => $request['vehicle_id'] ?? null,
            'venue_id'    => $request['venue_id'] ?? null,
        ];
        $query = DrivingRecord::select(
            'id',
            'user_name',
            'order_no',
            'phone',
            'venue_id',
            'venue_name',
            'vehicle_id',
            'vehicle_name',
            'payment_type',
            'reservation_status',
            'payment_amount',
            'start_time',
            'end_time',
            'order_time',
            'billing_method',
            'billing_rules',
            'special_area',
            'special_area_name')->where('reservation_status',4);

        if(isset($query_params['phone'])){
            $query->where('phone',$query_params['phone']);
        }

        if(isset($query_params['user_name'])){
            $query->where('user_name',$query_params['user_name']);
        }

        if(isset($query_params['order_no'])){
            $query->where('order_no',$query_params['order_no']);
        }

        if(isset($query_params['vehicle_id'])){
            $query->where('vehicle_id',$query_params['vehicle_id']);
        }

        if(isset($query_params['venue_id'])){
            $query->where('venue_id',$query_params['venue_id']);
        }
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        foreach ($rows as $value) {
            $value['driving_time'] = $value['end_time'] - $value['start_time'];
            $value['start_time'] = date('Y-m-d H:i:s',$value['start_time']);
            $value['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
            $value['order_time'] = date('Y-m-d H:i:s',$value['order_time']);
        }

        return ReponseData::reponsePaginationFormat($rows);
    }
}
