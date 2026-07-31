<?php
namespace App\Http\Service;

use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserEnergyLog;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Models\DepositLog;
use App\Models\ReponseData;
use App\Models\Vehicle;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected $setvice;
    public function __construct()
    {
        $this->setvice = new LoginService();
    }
    //列表
    public function list($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'phone_number'         => $request['phone_number'] ?? null,
            'nick_name'            => $request['nick_name'] ?? null,
            'is_cancel'            => $request['is_cancel'] ?? null,
            'special_area'         => $request['special_area'] ?? null,
            'start_register_time'  => $request['start_register_time'] ?? null,
            'end_register_time'    => $request['end_register_time'] ?? null,
            'is_locked'         => $request['is_frozen'] ?? null,
            'id'    => $request['id'] ?? null,

        ];
        $query = Cuser::select('id','username','phone_number','special_area','special_area_name','head_shot','is_real_name','real_name','register_time','is_locked','is_screenshot','show_id');
        $query = $query->where('is_delete','!=',1);
        $query = $query->where('is_cancel','!=',1);

        $special_area_name = CuserAgent::pluck('agent_name', 'id')
            ->toArray();
        if(isset($query_params['phone_number'])){
            $query->where('phone_number',$query_params['phone_number']);
        }

        if(isset($query_params['nick_name'])){
            $query->where('username', 'like', '%'.$query_params['nick_name'].'%');
        }

        if(isset($query_params['special_area'])){
            $query->where('special_area',$query_params['special_area']);
        }

        if(isset($query_params['is_cancel'])){
            $query->where('is_cancel',$query_params['is_cancel']);
        }

        if(isset($query_params['special_area'])){
            $query->where('special_area',$query_params['special_area']);
        }

        if(isset($query_params['id'])){
            $query->where('show_id',$query_params['id']);
        }
        if(isset($query_params['is_frozen'])){
            $query->where('is_locked',$query_params['is_locked']);
        }


        if(isset($query_params['start_register_time']) && isset($query_params['end_register_time'])){
            $query->whereBetween('register_time',[strtotime($query_params['start_register_time']),strtotime($query_params['end_register_time'])]);
        }

        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
//        $uids = array_column($rows->items(), 'id','special_area');

       /* $userBalanceWallet = CuserWallet::query()
            ->whereIn('uid', $uids)
            ->pluck('balance', 'uid')
            ->toArray();

        $userEnergyWallet = CuserWallet::query()
            ->whereIn('uid', $uids)
            ->pluck('energy', 'uid')
            ->toArray();*/

        $userMap = [];
        foreach ($rows as $item) {
        // 生成唯一键：uid_special_area
        $key = $item['id'] . '_' . $item['special_area'];
        $userMap[$key] = $item;
        }

        // 3. 构建批量查询的 组合条件数组
        $condition = [];
        foreach ($rows as $item) {
            $condition[] = [
                'uid' => $item['id'],
                'special_area' => $item['special_area']
            ];
        }

        // 4. 一次性批量查询（重点：where 嵌套 组合查询）
        $walletData = CuserWallet::query()
            ->where(function ($query) use ($condition) {
                foreach ($condition as $item) {
                    $query->orWhere(function ($q) use ($item) {
                        $q->where('uid', $item['uid'])
                            ->where('type', $item['special_area']);
                    });
                }
            })
            ->get()
            ->keyBy(function ($item) {
                // 用同样规则生成 key，方便匹配
                return $item->uid . '_' . $item->type;
            });
        foreach ($rows as &$value){
            $key = $value['id'] . '_' . $value['special_area'];
            $wallet = $walletData[$key] ?? null;
//            $value['balance'] = $userBalanceWallet[$value['id']] ?? 0;
//            $value['energy'] = $userEnergyWallet[$value['id'] ] ??  0;
            // 赋值钱包数据
            $value['balance'] = $wallet->balance ?? 0;
            $value['energy'] = $wallet->energy ?? 0;
            $value['register_time'] = date('Y-m-d H:i:s', $value['register_time']);
            $value['is_activation'] = $value['is_activation'] ?? 0;
            $value['is_frozen'] = $value['is_locked'] ?? 0;
            $value['special_area_name'] = $special_area_name[$value['special_area']];

            unset($value['is_locked']);
        }

        return ReponseData::reponsePaginationFormat($rows);
    }
    //详情
    public function details($request)
    {
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $user = Cuser::select('special_area_name','phone_number','nick_name','head_shot','is_real_name','real_name','is_screenshot')->where('id', $id)->first();
        $balance = CuserWallet::where('uid', $id)->value('balance');
        $user['balance'] = $balance;
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }

        return ReponseData::reponseFormatList(200,'成功',$user);
    }
    //后台余额列表
    public function modifyBalance($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        $page = $request['page'] ?? 1;
        $size = $request['size'] ?? 10;
        $start_time = $request['start_time'] ?? null;
        $end_time = $request['end_time'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $user = Cuser::where('id', $id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $userWallet = CuserWalletLog::select('id','type_name','type',
            'amount',
            'balance',
            'make_order_no',
            'venue',
            'special_area',
            'time')->where('uid', $user->id);
        if(!$userWallet){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($type){
            $userWallet = $userWallet->where('type',$type);
        }

        if(isset($start_time) && isset($end_time)){
            $userWallet->whereBetween('time',[strtotime($start_time),strtotime($end_time)]);
        }
        $rows = $userWallet->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);
        $record_id = array_column($rows->items(), 'make_order_no');
        $firstDepositLog = DepositLog::query()
            ->whereIn('order_no', $record_id)
            ->where('activity_id','!=','')
            ->pluck('activity_id', 'order_no')
            ->toArray();

        $sendMoney = DepositLog::query()
            ->whereIn('order_no', $record_id)
            ->where('activity_id','!=','')
            ->pluck('sendMoney', 'order_no')
            ->toArray();

        $special_area = array_column($rows->items(), 'special_area');
        $specialNames = CuserAgent::whereIn('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['activity_record_id'] = $firstDepositLog[$value['make_order_no']] ?? '0';
            $value['energy'] = $sendMoney[$value['make_order_no']] ?? '0';
            $value['time'] = date('Y-m-d H:i:s', $value['time']);
            $value['venue'] = $specialNames[$value['special_area']] ?? '';
        }
        return ReponseData::reponsePaginationFormat($rows);

    }
    public function modifyEnergy($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        $page = $request['page'] ?? 1;
        $size = $request['size'] ?? 10;
        $start_time = $request['start_time'] ?? null;
        $end_time = $request['end_time'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $user = Cuser::where('id', $id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $userWallet = CuserEnergyLog::select( 'id',
            'type',
            'type_name',
            'energy',
            'surplus_energy',
            'make_order_no',
            'venue',
            'recharge_amount',
            'activity_id',
            'time',
            'special_area')->where('uid', $user->id);

        if(!$userWallet){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($type){
            $userWallet = $userWallet->where('type',$type);
        }

        if(isset($start_time) && isset($end_time)){
            $userWallet->whereBetween('time',[strtotime($start_time),strtotime($end_time)]);
        }
        $rows = $userWallet->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);

        $special_area = array_column($rows->items(), 'special_area');
        $specialNames = CuserAgent::whereIn('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['time'] = date('Y-m-d H:i:s', $value['time']);
            $value['venue'] = $specialNames[$value['special_area']] ?? '';

        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function changePassword($request)
    {
        $password = $request['password'] ?? null;
        $id = $request['id'] ?? null;
        $phone = $request['phone'] ?? null;
        if(!$password){
            return ReponseData::reponseFormat(2002,'新密码必填');
        }
        if(!$phone){
            return ReponseData::reponseFormat(2002,'手机号必填');
        }

        if(!$id){
            return ReponseData::reponseFormat(2002,'id必填');

        }
        $user = Cuser::where('id', $id)->first();

        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到该账号!');
        }
        $user->phone_number = $phone;
        $user->password = md5($password);
        $user->save();
        return ReponseData::reponseFormat(200,'修改成功');

    }

    public function changeBalance($request)
    {
        $id = $request['id'] ?? null;
        $amount = $request['amount'] ?? null;
        $operator_name = $request['operator_name'] ?? '';
        $operator_account = $request['operator_account'] ?? '';

        if (!$id) {
            return ReponseData::reponseFormat(2002, 'id必填');

        }
        if (!$amount) {
            return ReponseData::reponseFormat(2002, '金额必填');

        }
        $user = Cuser::where('id', $id)->first();

        if (!$user) {
            return ReponseData::reponseFormat(2000, '未找到该账号!');

        }
        if($amount < 0){
            $balance = CuserWallet::where(['uid' => $id])->where('type',$user['special_area'])->first();
            if($balance['balance'] < abs($amount)){
                return ReponseData::reponseFormat(2000,'余额不能减为负数');
            }
        }
        try {
            WalletService::safeAdjust([
                'uid' => $user->id,
                'type' => CuserWalletLog::TypeChange,
                'type_name'=>'管理员修改余额',
                'make_order_no' => orderNo('CG'),
                'amount' => $amount,
                'venue'  => $user->special_area_name,
                'operator_name' => $operator_name,
                'operator_account' => $operator_account,
                'special_area' => $user->special_area,
            ]);
        }catch (\Exception $e){
            return ReponseData::reponseFormat(2000,$e->getMessage());
        }

        return ReponseData::reponseFormat(200,'修改成功');

    }

    public function changeEnergy($request)
    {
        $id = $request['id'] ?? null;
        $amount = $request['energy'] ?? null;
        $operator_name = $request['operator_name'] ?? '';
        $operator_account = $request['operator_account'] ?? '';
        if (!$id) {
            return ReponseData::reponseFormat(2002, 'id必填');

        }
        if (!$amount) {
            return ReponseData::reponseFormat(2002, '金额必填');

        }
        $user = Cuser::where('id', $id)->first();

        if (!$user) {
            return ReponseData::reponseFormat(2000, '未找到该账号!');

        }
        if($amount < 0){
            $balance = CuserWallet::where(['uid' => $id])->where('type',$user['special_area'])->first();
            if($balance['energy'] < abs($amount)){
                return ReponseData::reponseFormat(2000,'能量不能减为负数');
            }
        }
        try {
            WalletService::safeAdjustEnergy([
                'uid' => $user->id,
                'type' => CuserWalletLog::TypeChange,
                'type_name'=>'管理员修改能量',
                'make_order_no' => orderNo('CG'),
                'amount' => $amount,
                'venue'  => $user->special_area_name,
                'operator_name' => $operator_name,
                'operator_account' => $operator_account,
                'special_area' => $user->special_area,
                'special_area_name' => $user->special_area_name,
                'phone' => $user->phone_number,
            ]);
        }catch (\Exception $e){
            return ReponseData::reponseFormat(2000,$e->getMessage());
        }

        return ReponseData::reponseFormat(200,'修改成功');

    }

    public function frozen($request)
    {
        $id = $request['id'] ?? null;
        $frozen = $request['frozen'] ?? null;
        if(!$id) {
            return ReponseData::reponseFormat(2000, 'id必传!');
        }
        $user = Cuser::select('*')->where('id', $id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }

        if($frozen === null){
            return ReponseData::reponseFormat(2001,'冻结状态必传!');
        }
        if($frozen != 1){
            $frozen = 0;
        }
        $user->is_locked = $frozen;
        $user->save();
        return ReponseData::reponseFormat(200,'冻结成功');

    }
    public function delete($request)
    {
        $id = $request['id'] ?? null;
        if(!$id) {
            return ReponseData::reponseFormat(2000, 'id必传!');
        }
        $user = Cuser::select('*')->where('id', $id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $user->is_delete = 1;
        $user->save();
        return ReponseData::reponseFormat(200,'删除成功');
    }

    public function specialList($request)
    {

        $specialList = CuserAgent::select('id','agent_name')->where('superior_agent_id',0)->get();
        return  ReponseData::reponseFormatList(200,'成功',$specialList);
    }

    public function changeBalanceLog($request)
    {
        $special_area = $request['special_area'] ?? null;
        $page = $request['page'] ?? 1;
        $size = $request['size'] ?? 10;
        $start_time = $request['start_time'] ?? null;
        $end_time = $request['end_time'] ?? null;
        $username = $request['username'] ?? null;
        $phone = $request['phone'] ?? null;
        $operator_name = $request['operator_name'] ?? null;
        $operator_account = $request['operator_account'] ?? null;

        $userWallet = CuserWalletLog::select('id','type_name','type',
            'amount',
            'balance',
            'make_order_no',
            'user_name',
            'operator_name',
            'operator_account',
            'phone',
            'venue',
            'time',
            'special_area')->where('type',4);

        if(!$userWallet){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($username){
            $userWallet = $userWallet->where('user_name',$username);
        }
        if($operator_name){
            $userWallet = $userWallet->where('operator_name',$operator_name);
        }
        if($operator_account){
            $userWallet = $userWallet->where('operator_account',$operator_account);
        }
        if($phone){
            $userWallet = $userWallet->where('phone',$phone);
        }
        if($special_area){
            $userWallet = $userWallet->where('special_area',$special_area);
        }

        if(isset($start_time) && isset($end_time)){
            $userWallet->whereBetween('time',[strtotime($start_time),strtotime($end_time)]);
        }


        $rows = $userWallet->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);
        $special_area = array_column($rows->items(), 'special_area');
        $specialNames = CuserAgent::whereIn('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
            $value['venue'] = $specialNames[$value['special_area']] ?? '';

        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function changeEnergyLog($request)
    {
        $special_area = $request['special_area'] ?? null;
        $page = $request['page'] ?? 1;
        $size = $request['size'] ?? 10;
        $start_time = $request['start_time'] ?? null;
        $end_time = $request['end_time'] ?? null;
        $username = $request['username'] ?? null;
        $phone = $request['phone'] ?? null;
        $operator_name = $request['operator_name'] ?? null;
        $operator_account = $request['operator_account'] ?? null;

        $userWallet = CuserEnergyLog::select('id','type_name','type',
            'energy',
            'surplus_energy',
            'make_order_no',
            'user_name',
            'operator_name',
            'operator_account',
            'special_area',
            'special_area_name',
            'phone',
            'venue',
            'time')->where('type',4);

        if(!$userWallet){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($username){
            $userWallet = $userWallet->where('user_name',$username);
        }
        if($operator_name){
            $userWallet = $userWallet->where('operator_name',$operator_name);
        }
        if($operator_account){
            $userWallet = $userWallet->where('operator_account',$operator_account);
        }
        if($phone){
            $userWallet = $userWallet->where('phone',$phone);
        }
        if($special_area){
            $userWallet = $userWallet->where('special_area',$special_area);
        }

        if(isset($start_time) && isset($end_time)){
            $userWallet->whereBetween('time',[strtotime($start_time),strtotime($end_time)]);
        }


        $rows = $userWallet->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);
        $special_area = array_column($rows->items(), 'special_area');
        $specialNames = CuserAgent::whereIn('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
            $value['venue'] = $specialNames[$value['special_area']] ?? '';
        }
        return ReponseData::reponsePaginationFormat($rows);
    }


    public function walletList($request)
    {
        $id = $request['id'] ?? null;
        $size = $request['size'] ?? 20;
        $page = $request['page'] ?? 1;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }

        $cuser = Cuser::where('id',$id)->first();

        if(!$cuser){
            return ReponseData::reponseFormat(2000,'未找到该用户');
        }

        $walletRecord = CuserWallet::select('id','uid','balance','type')->where('uid',$cuser['id']);
        $rows = $walletRecord->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);

        $special_area = array_column($rows->items(), 'type');
        $specialNames = CuserAgent::whereIn('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['special_area_name'] = $specialNames[$value['type']] ?? '';
        }

        return ReponseData::reponsePaginationFormat($rows);
    }

    public function changeWalletBalance($request)
    {
        $id = $request['id'] ?? null;
        $amount = $request['amount'] ?? null;
        $operator_name = $request['operator_name'] ?? '';
        $operator_account = $request['operator_account'] ?? '';
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }

        $cuserWallet = CuserWallet::where('id',$id)->first();

        if(!$cuserWallet){
            return ReponseData::reponseFormat(2000,'未找到该余额记录');
        }
        $user = Cuser::where('id',$cuserWallet->uid)->first();
        if(!$user){
            return  ReponseData::reponseFormat(2000,'未找到该用户');
        }
        $typeName = '管理员增加余额';

        if($amount < 0){
            $balance = CuserWallet::where('id',$id)->first();
            if($balance['balance'] < abs($amount)){
                return ReponseData::reponseFormat(2000,'余额不能减为负数');
            }
            $typeName = '管理员扣减余额';
        }

//        $specialNames = CuserAgent::where('id',$cuserWallet['type'])->pluck('agent_name','id');
        try {
            WalletService::safeAdjust([
                'uid' => $user->id,
                'type' => CuserWalletLog::TypeChange,
                'type_name'=>$typeName,
                'make_order_no' => orderNo('CG'),
                'amount' => $amount,
                'venue'  => $user->special_area_name,
                'operator_name' => $operator_name,
                'operator_account' => $operator_account,
                'special_area' => $cuserWallet['type'],
            ]);
        }catch (\Exception $e){
            return ReponseData::reponseFormat(2000,$e->getMessage());
        }

        return ReponseData::reponseFormat(200,'修改成功');
    }

    public function updateScreenshot($request)
    {

        $id = $request['id'] ?? null;
        $isScreenshot = $request['is_screenshot'] ?? null;
        if(!$id){
            ReponseData::reponseFormat(2000,'id必传');
        }

        $user = Cuser::where('id',$id)->first();

        if(!$user){

            return ReponseData::reponseFormat(2000,'未找到该用户');
        }

        if($isScreenshot === null){
            ReponseData::reponseFormat(2000,'是否开启截图权限字段必填');
        }

        $user->update(['is_screenshot' => $isScreenshot]);

        return ReponseData::reponseFormat(200,'更新成功');
    }


    public function walletDetail($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        $page = $request['page'] ?? 1;
        $size = $request['size'] ?? 10;
        $start_time = $request['start_time'] ?? null;
        $end_time = $request['end_time'] ?? null;


        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $cuserWallet= CuserWallet::where('id', $id)->first();
        if(!$cuserWallet){
            return ReponseData::reponseFormat(2001,'未找到该专区余额记录哦!');
        }
        $user = Cuser::where('id',$cuserWallet['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'为找到该用户');
        }
        $special_area = $cuserWallet['type'];
        $userWallet = CuserWalletLog::select('id','type_name','type',
            'amount',
            'balance',
            'make_order_no',
            'venue',
            'special_area',
            'time')->where('uid', $user->id)->where('special_area',$special_area);
        if(!$userWallet){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($type){
            $userWallet = $userWallet->where('type',$type);
        }

        if(isset($start_time) && isset($end_time)){
            $userWallet->whereBetween('time',[strtotime($start_time),strtotime($end_time)]);
        }
        $rows = $userWallet->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);
        $record_id = array_column($rows->items(), 'make_order_no');
        $firstDepositLog = DepositLog::query()
            ->whereIn('order_no', $record_id)
            ->where('activity_id','!=','')
            ->pluck('activity_id', 'order_no')
            ->toArray();

        $sendMoney = DepositLog::query()
            ->whereIn('order_no', $record_id)
            ->where('activity_id','!=','')
            ->pluck('sendMoney', 'order_no')
            ->toArray();

        $special_area = array_column($rows->items(), 'special_area');
        $specialNames = CuserAgent::where('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['activity_record_id'] = $firstDepositLog[$value['make_order_no']] ?? '0';
            $value['energy'] = $sendMoney[$value['make_order_no']] ?? '0';
            $value['time'] = date('Y-m-d H:i:s', $value['time']);
            $value['venue'] = $specialNames[$value['special_area']] ?? '';
        }
        return ReponseData::reponsePaginationFormat($rows);

    }
}
