<?php

namespace App\Http\Service;


use App\Models\DepositActivity;
use App\Models\DepositLog;
use App\Models\ReponseData;

class DepositActivityService
{

    public function List($request)
    {
        $data = [
            'activity_id' => $request['activity_id'] ?? null,
            'type' => $request['type'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];




        $list = DepositActivity::select('*');
        if($data['activity_id']){
            $list->where('activity_id',$data['activity_id']);
        }

        if($data['type']){
            $list->where('type',$data['type']);
        }
        if($data['type'] != 1 && $data['type'] != null){
            $list->where('type',0);
        }

        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function Create($request)
    {
        $data = [
            'activity_id' => $request['activity_id'] ?? mt_rand(100000000,999999999),
            'payment_amount'=> $request['payment_amount'] ?? null,
            'send_energy' => $request['send_energy'] ?? null,
            'num' => $request['num'] ?? null,
            'type' => $request['type'] ?? null,
            'sort' => $request['sort'] ?? null,
            'remark' => $request['remark'] ?? '',
        ];
        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'状态必填');
        }
        if(!$data['payment_amount']){
            return ReponseData::reponseFormat(2000,'充值金额必填');
        }
        if($data['num'] === null){
            return ReponseData::reponseFormat(2000,'次数必填');
        }
        if($data['sort'] === null){
            return ReponseData::reponseFormat(2000,'排序必填');
        }

        if(!$data['activity_id']){
            return ReponseData::reponseFormat(2000,'活动id必填');
        }

        if(!$data['send_energy']){
            return ReponseData::reponseFormat(2000,'赠送能量必填');
        }



        DepositActivity::create($data);

        return ReponseData::reponseFormat(200,'成功');

    }

    public function Update($request)
    {
        $id = $request['id'] ?? null;

        if(!$id){
            return  ReponseData::reponseFormat(2000,'id必传');
        }
        $list = DepositActivity::where('id',$id)->first();
        $data = [
            'activity_id' => $request['activity_id'] ?? $list['activity_id'],
            'payment_amount'=> $request['payment_amount'] ?? $list['payment_amount'],
            'send_energy' => $request['send_energy'] ?? $list['send_energy'],
            'num' => $request['num'] ?? $list['num'],
            'type' => $request['type'] ?? $list['type'],
            'sort' => $request['sort'] ?? $list['sort'],
            'remark' => $request['remark'] ?? '',
        ];
        if(!$list){
            return  ReponseData::reponseFormat(2000,'未找到该数据');
        }
        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'状态必填');
        }
        if(!$data['payment_amount']){
            return ReponseData::reponseFormat(2000,'充值金额必填');
        }
        if($data['num'] === null){
            return ReponseData::reponseFormat(2000,'次数必填');
        }
        if($data['sort'] === null){
            return ReponseData::reponseFormat(2000,'排序必填');
        }

        if(!$data['activity_id']){
            return ReponseData::reponseFormat(2000,'活动id必填');
        }

        if(!$data['send_energy']){
            return ReponseData::reponseFormat(2000,'赠送能量必填');
        }



        $list->update($data);

        return ReponseData::reponseFormat(200,'成功');

    }
    public function Delete($request)
    {
        $id = $request['id'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $list = DepositActivity::where('id', $id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该条数据');
        }
        $list->delete();

        return ReponseData::reponseFormat(200,'成功');
    }

    public function ChangeStatus($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        if($type === null){
            return ReponseData::reponseFormat(2000,'状态必传');
        }
        $list = DepositActivity::where('id', $id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该条数据');
        }
        $list->update(['type' => $type]);

        return ReponseData::reponseFormat(200,'成功');
    }

    public function Record($request)
    {
        $data = [
            'activity_id' => $request['activity_id'] ?? null,
            'user_name' => $request['user_name'] ?? null,
            'phone_number' => $request['phone_number'] ?? null,
            'special_area' => $request['special_area'] ?? null,
            'pay_id' => $request['pay_id'] ?? null,
            'energy_id' => $request['energy_id'] ?? null,
            'start_time' => $request['start_time'] ?? null,
            'end_time' => $request['end_time'] ?? null,
            'min_amount' => $request['min_amount'] ?? null,
            'max_amount' => $request['max_amount'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];




        $list = DepositLog::select(
            'id',
            'activity_id',
            'special_area_name',
            'user_name',
            'phone_number',
            'amount',
            'sendMoney',
            'pay_id',
            'energy_id',
            'time',
            'uid',
            'special_area',
            'type')->where('activity_id','!=', '');
        if($data['activity_id']){
            $list->where('activity_id',$data['activity_id']);
        }


        if($data['user_name']){
            $list->where('user_name',$data['user_name']);
        }

        if($data['phone_number']){
            $list->where('phone_number',$data['phone_number']);
        }
        if($data['special_area']){
            $list->where('special_area',$data['special_area']);
        }

        if($data['pay_id']){
            $list->where('pay_id',$data['pay_id']);
        }
        if($data['energy_id']){
            $list->where('energy_id',$data['energy_id']);
        }

        if($data['start_time'] && $data['end_time']){
            $list->wheheBetween('time',$data['start_time'],$data['end_time']);
        }
        if($data['min_amount'] && $data['max_amount']){
            $list->wheheBetween('amount',$data['min_amount'],$data['max_amount']);
        }

        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        $activity_id = array_column($rows->items(), 'id');
        $depositActivity = DepositActivity::query()
            ->whereIn('activity_id', $activity_id)
            ->pluck('num', 'activity_id')
            ->toArray();
        foreach($rows as $row){
            $row['num'] = $depositActivity[$row['activity_id']] ?? '';
            $row['send_money'] = $row['sendMoney'] ?? '';
            unset($row['sendMoney']);
            $row['time'] = date('Y-m-d H:i:s',$row['time']);
        }
        return ReponseData::reponsePaginationFormat($rows);
    }
}
