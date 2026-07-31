<?php

namespace App\Http\Service;

use App\Models\AgentWallet;
use App\Models\AgentWalletLog;
use App\Models\AgentWithdrawLog;
use App\Models\ComplainRecord;
use App\Models\CuserAgent;
use App\Models\CuserWallet;
use App\Models\DataCollect;
use App\Models\DepositLog;
use App\Models\DrivingRecord;
use App\Models\ReponseData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PaymentService
{

    public function paymentList($request)
    {
        $data = [
            'pay_type' => $request['pay_type'] ?? null,
            'user_name' => $request['user_name'] ?? null,
            'phone_number' => $request['phone'] ?? null,
            'order_no' => $request['order_no'] ?? null,
            'pay_id' => $request['pay_id'] ?? null,
            'activity_id' => $request['activity_id'] ?? null,
            'start_finish_time' => $request['start_finish_time'] ?? null,
            'end_finish_time' => $request['end_finish_time'] ?? null,
            'start_time'  => $request['start_time'] ?? null,
            'end_time'  => $request['end_time'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];




        $list = DepositLog::select('*')->where('type',1);
        if($data['pay_type']){
            $list->where('pay_type',$data['pay_type']);
        }

        if($data['user_name']){
            $list->where('user_name','like', '%'.$data['user_name'].'%');
        }

        if($data['phone_number']){
            $list->where('phone_number',$data['phone_number']);
        }

        if($data['order_no']){
            $list->where('third_order_no',$data['order_no']);
        }

        if($data['pay_id']){
            $list->where('pay_id',$data['pay_id']);
        }
        if($data['activity_id']){
            $list->where('activity_id',$data['activity_id']);
        }

        if($data['start_finish_time'] && $data['end_finish_time']){
            $list->whereBetween('finish_time',[$data['start_finish_time'],$data['end_finish_time']]);
        }
        if($data['start_time'] && $data['end_time']){
            $list->whereBetween('time',[$data['start_time'],$data['end_time']]);
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        $special_area_name = CuserAgent::pluck('agent_name', 'id')
            ->toArray();
        foreach($rows as $row){
            $row['finish_time'] = date('Y-m-d H:i:s',$row['finish_time']);
            $row['time'] = date('Y-m-d H:i:s',$row['time']);
            $row['special_area_name'] = $special_area_name[$row['special_area']];
        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function withdrawList($request)
    {
        $data = [
            'agent_name' => $request['agent_name'] ?? null,
            'withdraw_type' => $request['withdraw_type'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];




        $list = AgentWithdrawLog::select('*');
        if($data['agent_name']){
            $list->where('agent_name',$data['agent_name']);
        }

        if($data['withdraw_type']){
            $list->where('withdraw_type',$data['withdraw_type']);
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        foreach($rows as $row){
            $row['enrolment_time'] = date('Y-m-d H:i:s',$row['enrolment_time']);
            $row['audit_time'] = date('Y-m-d H:i:s',$row['audit_time']);

        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function refundList($request)
    {
        $data = [
            'user_name' => $request['agent_name'] ?? null,
            'phone' => $request['phone'] ?? null,
            'refund_type' => $request['refund_type'] ?? null,
            'order_no'  => $request['order_no'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];




        $list = ComplainRecord::select('order_no','uid','user_name','phone','refund_type','refund_amount','refund_cause','vehicle_name','time');
//        $list->where('refund_type','!=',0);
        if($data['user_name']){
            $list->where('user_name',$data['user_name']);
        }

        if($data['phone']){
            $list->where('phone',$data['phone']);
        }

        if($data['refund_type']){
            $list->where('refund_type',$data['refund_type']);
        }

        if($data['order_no']){
            $list->where('order_no',$data['order_no']);
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        foreach($rows as $row){
            $row['time'] = date('Y-m-d H:i:s',$row['time']);
        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function specialAccountList($request)
    {
        $data = [
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];
        $list = CuserAgent::select('id','agent_name','phone_number')->where('superior_agent_id',0);
        $agents = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);

        if($agents->isNotEmpty()){
            foreach($agents as $agent){
                $agent['count'] = CuserWallet::where('type',$agent['id'])->count();
            }
        }

        return ReponseData::reponsePaginationFormat($agents);

    }

    public function specialDepositList($request)
    {
        $data = [
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
            'phone' =>  $request['phone'] ?? null,
            'special_area' =>   $request['special_area'] ?? null,
            'start_time' => $request['start_time'] ?? null,
            'end_time' => $request['end_time'] ?? null,
        ];
        if($data['special_area']){
            $list = CuserAgent::select('id','agent_name','phone_number','superior_agent_id')->where('id',$data['special_area']);
        }else{
            $list = CuserAgent::select('id','agent_name','phone_number','superior_agent_id')->where('superior_agent_id',0);
        }

        if($data['phone']){
            $list->where('phone_number',$data['phone']);
        }
        $agents = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        if($data['start_time'] && $data['end_time']) {
            $data['start_time'] = strtotime($data['start_time'] . ' 00:00:00');
            $data['end_time'] = strtotime($data['end_time'] . ' 23:59:59');
        }
        if($agents->isNotEmpty()){

            foreach($agents as $agent){
                if($data['start_time'] && $data['end_time']){
                    $agent['deposit_amount'] = DepositLog::where('special_area',$agent['id'])->where('type',1)->whereBetween('time',[$data['start_time'],$data['end_time']])->sum('amount');
                }else{
                    $agent['deposit_amount'] = DepositLog::where('special_area',$agent['id'])->where('type',1)->sum('amount');
                }
                $agent['balance'] = CuserWallet::where('type',$agent['id'])->sum('balance');
            }
        }

        return ReponseData::reponsePaginationFormat($agents);

    }

    public function withdrawAudit($request)
    {
        $id = $request['id'];
        $status = $request['status'] ?? 0;
        $key = 'check_withdraw'.'_'.$id;
        $ret = Redis::set($key, '1','ex','1','nx');

        if(!$ret){
            return ReponseData::reponseFormat(2000,'请勿重复点击');
        }
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }

        $list = AgentWithdrawLog::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }

        if($status == 2){
            $returnAmount = $list['withdraw_amount'];
            $agent = CuserAgent::where('id',$list['agent_id'])->first();
            $agentWallet = AgentWallet::getBalance($list['agent_id']);
            $balance = $agentWallet['balance'];

            $updateQuery = AgentWallet::where(['agent_id' => $list['agent_id']]);
            $affected = $updateQuery->update(['balance' => DB::raw("balance+{$returnAmount}")]);
            if ($affected != 1) {
                Log::info("提现审核失败回退金额： {$returnAmount}, 增加失败： {$agentWallet['balance']}");
            }
            $afterBalance = $balance + $returnAmount;

            AgentWalletLog::create([
                'agent_id' => $list['agent_id'],
                'type' => 1,
                'type_name' => '提现审核失败回退金额',
                'amount' => $returnAmount,
                'balance' => $afterBalance,
                'time' => time(),
            ]);

        }

        $list->status = $status;
        $list->audit_time = time();
        $list->save();

        return ReponseData::reponseFormat(200,'成功');
    }

    public function Index($request)
    {
        $data = DataCollect::select('total_sale', 'total_make', 'total_payment', 'total_refund')->where('id',1)->first();
        $start_time = strtotime(date('Y-m-d').' 00:00:00');
        $end_time = strtotime(date('Y-m-d').' 23:59:59');
//        $data['today_sale'] = DrivingRecord::where('reservation_status',4)->where('payment_type',1)->whereBetween('order_time', [$start_time, $end_time])->sum('payment_amount');
        $data['today_sale'] = DepositLog::where('type',1)->whereBetween('time', [$start_time, $end_time])->sum('amount');
        $data['today_make'] = DrivingRecord::whereBetween('order_time', [$start_time, $end_time])->count();
        $data['today_payment'] = DepositLog::where('type',1)->whereBetween('time', [$start_time, $end_time])->count();
        $data['today_refund'] = ComplainRecord::where('refund_type',1)->whereBetween('time', [$start_time, $end_time])->sum('refund_amount');
        $data['total_sale'] = $data['total_sale'] + $data['today_sale'];
        $data['total_make'] = $data['total_make'] + $data['today_make'];
        $data['total_payment'] = $data['total_payment'] + $data['today_payment'];
        $data['total_refund'] = $data['total_refund'] + $data['today_refund'];

        $month = [];
        for($i = 0; $i <= 9; $i++){
            $currentDate = Carbon::now()->subMonths($i);
            $monthStart = $currentDate->copy()->startOfMonth()->timestamp;
            // 月末：当月最后一天 23:59:59
            $monthEnd = $currentDate->copy()->endOfMonth()->timestamp;
            $month[$currentDate->format('Y-m')]= DrivingRecord::whereBetween('order_time', [$monthStart, $monthEnd])->count();
        }
        $data['month_num'] = $month;
        return ReponseData::reponseFormatList(200,'成功',$data);
    }
}
