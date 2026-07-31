<?php

namespace App\Http\Service;


use App\Models\AgentVenue;
use App\Models\AgentWallet;
use App\Models\AgentWalletLog;
use App\Models\AgentWithdrawLog;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Models\DrivingRecord;
use App\Models\ReponseData;
use App\Models\Vehicle;
use App\Models\VehicleConfig;
use Doctrine\DBAL\Types\Type;
use http\Env\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use MongoDB\Driver\ReadPreference;

class AgentService
{

    protected $TypeValue = [
        1=>'遥控车',
        2=>'遥控船',
        3=>'工程车',
    ];
    //代理前台用户余额
    public function agentMine($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $agent_id = $request['agent_id'] ?? null;
        if(!$agent_id){
            return ReponseData::reponseFormat(2000,'agent_id必传!');
        }
        $user = CuserAgent::select('id','agent_name','head_shot','show_id','phone_number','is_screenshot','wallet_password')->where('id', $agent_id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($user['wallet_password'] != ''){
            $user['is_password'] = 1;
        }else {
            $user['is_password'] = 0;
        }
        unset($user['wallet_password']);
        $balance  = AgentWallet::getBalance($user['id']);
        $user['balance'] = $balance['balance'];
        $user['wechat_service_url'] = env('WECHAT_SERVICE_URL','');
        $user['wx_corp_id'] = env('WX_CORP_ID','');
        $user['business_wechat'] = env('BUSINESS_WECHAT','we1731747901') ;
        $user['business_email'] = env('BUSINESS_EMAIL','fuhuanyong@sqzskj.cn') ;

        return ReponseData::reponseFormatList(200,'成功',$user);
    }

    public function agentDrivingRecord($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $agent_id = $request['agent_id'] ?? null;
        $size = $request['size'] ?? 10;
        $page = $request['page'] ?? 1;

        if(!$agent_id){
            return ReponseData::reponseFormat(2000,'agent_id必传!');
        }
        $user = CuserAgent::where('id', $agent_id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $vehicle = Vehicle::where('agent_id', $agent_id)->pluck('id');
        $query = DrivingRecord::select('id','uid','agent_id','head_shot','vehicle_id','reservation_status','user_name','order_no','venue_name','vehicle_name','billing_method','order_time','start_time','end_time','payment_type','payment_amount');


//        if($user['superior_agent_id'] != 0){
            $query = $query->where('agent_id', $agent_id);
            $query = $query->whereIn('vehicle_id', $vehicle);
        $query = $query->where('reservation_status',4);

        $rows = $query->orderBy("order_time", 'desc')->paginate($size, ['*'], 'page', $page);
        if ($rows->isEmpty()) {
            return ReponseData::reponsePaginationFormat($rows);

        }
        $uids = collect($rows->items())->pluck('uid')->unique()->filter()->toArray();
//        $uids = $query->pluck('uid');
        $userUserName = Cuser::query()
            ->whereIn('id', $uids)
            ->pluck('username', 'id')
            ->toArray();

        $userUserHeadShot = Cuser::query()
            ->whereIn('id', $uids)
            ->pluck('head_shot', 'id')
            ->toArray();
//        }else{
//            $query = $query->where('agent_id',  $user['superior_agent_id']);
//        }
        foreach($rows as $row){
            $row['user_name'] = $userUserName[$row['uid']] ?? $row['user_name'];
            $row['head_shot'] = $userUserHeadShot[$row['uid']] ??  $row['head_shot'];
        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function agentDriving($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $agent_id = $request['agent_id'] ?? null;
//        $agentId = $request['agent_id'] ?? null;


        if(!$agent_id){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }

        $user = CuserAgent::where('id', $agent_id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $vehicle = Vehicle::where('agent_id', $agent_id)->pluck('id');
        if($user['superior_agent_id'] != 0){
            $list = DrivingRecord::select('id','agent_id','head_shot','user_name','order_no','vehicle_name','billing_method','order_time','start_time','end_time','payment_amount')
                ->where('agent_id', $agent_id)
                ->where('reservation_status',3)
                ->orderBy("order_time", 'desc')
                ->get();
        }else{
            $list = DrivingRecord::select('id','agent_id','head_shot','user_name','order_no','vehicle_name','billing_method','order_time','start_time','end_time','payment_amount')
                ->where('agent_id', $agent_id)
                ->where('reservation_status',3)
                ->orderBy("order_time", 'desc')
                ->get();
        }




        return ReponseData::reponseFormatList(200,'获取成功',$list);
    }
    public function agentWalletLog($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'type'         => $request['type'] ?? null,
            'agent_id'            => $request['agent_id'] ?? null,
        ];
        $query = AgentWalletLog::select('agent_id', 'type', 'type_name','make_order_no', 'amount','time');
        if(!$query_params['agent_id']){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $query = $query->where('agent_id', $query_params['agent_id']);
        if($query_params['type']){
            $query = $query->where('type', $query_params['type']);
        }
        $rows = $query->orderBy("time", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        return ReponseData::reponsePaginationFormat($rows);
    }

    //后台管理
    public function list($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'phone_number'         => $request['phone_number'] ?? null,
            'agent_name'            => $request['agent_name'] ?? null,
            'is_cancel'            => $request['is_cancel'] ?? null,
            'level'                 =>  $request['level'] ?? null,
        ];
        $query = CuserAgent::select('id',
            'agent_name',
            'level',
            'phone_number',
            'venue_quantity',
            'create_site_quantity',
            'is_support',
            'head_shot',
            'provinces',
            'city',
            'register_time',
            'review_status',
            'support_status',
            'is_cancel',
            'sorting',
            'yesterday_turnover',
            'superior_agent_id',
            'withdrawal_amount',
            'first_handling_fee',
            'company_handling_fee',
            'is_frozen',
            'type')->where('is_delete','!=',1);

        if(isset($query_params['phone_number'])){
            $query->where('phone_number',$query_params['phone_number']);
        }

        if(isset($query_params['agent_name'])){
            $query->where('agent_name','like', "%{$query_params['agent_name']}%");
        }

        if(isset($query_params['is_cancel'])){
            $query->where('is_cancel',$query_params['is_cancel']);
        }

        if(isset($query_params['level'])){
            $query->where('level',$query_params['level']);
        }

        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        $agent_ids = array_column($rows->items(), 'id');
        $userBalanceWallet = AgentWallet::query()
            ->whereIn('agent_id', $agent_ids)
            ->pluck('balance', 'agent_id')
            ->toArray();


        foreach ($rows as $value){
            $value['balance'] = $userBalanceWallet[$value['id'] ?? '0'];//余额
            $value['first_handling_fee'] = $value['first_handling_fee'] . '%';//一级代理商抽成
            $value['company_handling_fee'] = $value['company_handling_fee'] . '%';//公司抽成
            $value['register_time'] = date('Y-m-d H:i:s', $value['register_time']);
            if($value['level'] == 2){
                $superior_agent_name = CuserAgent::where('id', $value['superior_agent_id'])->value('agent_name');
            }else{
                $superior_agent_name = '掌控视界';
            }
            $value['superior_agent_name'] = $superior_agent_name;

        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function create($request)
    {
        $data = [
            'level' => $request['level'] ?? null, //代理商等级
            'superior_agent_id' => $request['superior_agent_id'] ?? 0, //上级代理商id
            'phone_number'=>$request['phone_number'] ?? null, //手机号
            'head_shot' => $request['head_shot'] ?? 'https://zksj-new.oss-cn-beijing.aliyuncs.com/zk/image/ZKSJ_1770280030SR25.jpeg', //头像
            'create_site_quantity' => $request['create_site_quantity'] ?? null, //可创建场地数量
            'sorting'=>$request['sorting'] ?? 1,//排序号
            'is_support' => $request['is_support'] ?? null, //是否自营
            'agent_name' => $request['agent_name'],
            'first_handling_fee'=>$request['first_handling_fee'] ?? 0,
            'company_handling_fee'=>$request['company_handling_fee'] ?? 0,
            'password' => md5($request['password']) ?? null,
            'register_time'=>time(),
            'show_id' => mt_rand(10000000,99999999),
            'type'=>$request['type'] ?? 3,
        ];
        if(!$data['level']){
            return ReponseData::reponseFormat(2000,'代理商等级必填');
        }
        if(!$data['password']){
            return ReponseData::reponseFormat(2000,'密码必传');

        }

        if($data['level'] == 2){
            if(!isset($data['superior_agent_id'])){
                return ReponseData::reponseFormat(2000,'上级代理商必填');
            }
        }


        if(!$data['phone_number']){
            return ReponseData::reponseFormat(2000,'手机号必填');
        }

        if(!$data['create_site_quantity']){
            return ReponseData::reponseFormat(2000,'可创建场地总数必填');
        }

        if($data['is_support'] === null){
            return ReponseData::reponseFormat(2000,'是否自营必填');
        }
//        $data['password'] = md5($request['password']);
        $cuserAgent = CuserAgent::create($data);
        AgentWallet::getBalance($cuserAgent['id']);

        return ReponseData::reponseFormat(200,'新增成功');

    }

    public function update($request)
    {
        $id = $request['id'];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        $list = CuserAgent::where('id', $id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }
        $data = [
            'head_shot' => $request['head_shot'] ?? $list['head_shot'], //头像
            'create_site_quantity' => $request['create_site_quantity'] ?? null, //可创建场地数量
            'sorting'=>$request['sorting'] ?? $list['sorting'], ///排序号
            'is_support' => $request['is_support'] ?? null, //是否自营
            'agent_name' => $request['agent_name'] ?? $list['agent_name'],
            'first_handling_fee'=>$request['first_handling_fee'] ?? $list['first_handling_fee'],
            'company_handling_fee'=>$request['company_handling_fee'] ?? $list['company_handling_fee'],
            'password' => md5($request['password']) ?? $list['password'],
            'type' => $request['type'] ?? 3,
        ];


        if(!$data['create_site_quantity']){
            return ReponseData::reponseFormat(2000,'可创建场地总数必填');
        }
        if(!$data['password']){
            return ReponseData::reponseFormat(2000,'');
        }

        if($data['is_support'] === null){
            return ReponseData::reponseFormat(2000,'是否自营必填');
        }

        $list->update($data);

        return ReponseData::reponseFormat(200,'更新成功');

    }

    public function detail($request)
    {
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $cuserAgent = CuserAgent::select('*')->where('id', $id)->first();
        if(!$cuserAgent){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $superior_agent_name = CuserAgent::where('id', $cuserAgent['superior_agent_id'])->value('agent_name');
        $cuserAgent['register_time'] = date('Y-m-d H:i:s', $cuserAgent['register_time']);
        $cuserAgent['superior_agent_name'] = $superior_agent_name ?? '';

        return ReponseData::reponseFormatList(200,'成功',$cuserAgent);
    }

    public function vehicleList($request)
    {
        $data = [
            'agent_id' => $request['id'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
            'name' => $request['name'] ?? null,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $exists = CuserAgent::where('id', $data['agent_id'])->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该代理!');
        }

        $list = Vehicle::select('id','vehicle_name','vehicle_image','vehicle_state','vehicle_battery','top_speed','status','created_at')->where(['agent_id'=>$data['agent_id']]);
        if($data['name']){
            $list->where('vehicle_name','like', "%{$data['name']}%");
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);

        return ReponseData::reponsePaginationFormat($rows);
    }

    public function vehicleDetail($request){
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $vehicle = Vehicle::where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2001,'未找到该车辆!');
        }
        $vehicleConfig = VehicleConfig::where('vehicle_id', $id)->first();
        if(!$vehicleConfig){
            return ReponseData::reponseFormat(2001,'未找到该车辆配置!');
        }
        $agent = CuserAgent::where('id', $vehicle['agent_id'])->first();
        $vehicleConfig['vehicle_name'] = $vehicle['vehicle_name'];
        $vehicleConfig['vehicle_battery'] = $vehicle['vehicle_battery'];
        $vehicleConfig['top_speed'] = $vehicle['top_speed'];
        $vehicleConfig['vehicle_introduction'] = $vehicle['vehicle_introduction'];
        $vehicleConfig['transmitter_id'] = $vehicle['transmitter_id'];
        $vehicleConfig['receiver_id'] = $vehicle['receiver_id'];
        $vehicleConfig['user_name'] = $agent['agent_name'] ?? '';
        $vehicleConfig['phone'] = $agent['phone_number'] ?? '';
        if($vehicle['venue_id'] != 0){
            $vehicleConfig['binding_status'] = '已绑定';
        }else{
            $vehicleConfig['binding_status'] = '未绑定';
        }
        $vehicleConfig['vehicle_state'] = $vehicle['vehicle_state'];
        $vehicleConfig['front_camera'] = $vehicle['front_camera'];
        $vehicleConfig['rear_camera'] = $vehicle['rear_camera'];
        $vehicleConfig['vehicle_config_detail'] = json_decode($vehicleConfig['vehicle_config_detail']);


        return ReponseData::reponseFormatList(200,'成功!',$vehicleConfig);
    }

    public function walletLog($request)
    {
        $id = $request['id'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $cuserAgent = CuserAgent::select('*')->where('id', $id)->first();
        if(!$cuserAgent){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }

        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'type'         => $request['type'] ?? null,
            'start_time'            => $request['start_time'] ?? null,
            'end_time'            => $request['end_time'] ?? null,
        ];
        $query = AgentWalletLog::select('id','type', 'type_name', 'amount', 'balance', 'make_order_no', 'venue', 'user_name', 'phone', 'time')->where(['agent_id'=>$id]);
        if($query_params['type']){
            $query->where('type', $query_params['type']);
        }
        if($query_params['start_time'] && $query_params['end_time']){
            $query->whereBetween('time', [$query_params['start_time'], $query_params['end_time']]);
        }
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        foreach($rows as $value){
            $value['time'] = date('Y-m-d H:i:s', $value['time']);
        }
        return  ReponseData::reponsePaginationFormat($rows);

    }

    public function changePassword($request)
    {
        $id = $request['id'] ?? null;
        $password = $request['password'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $cuserAgent = CuserAgent::select('*')->where('id', $id)->first();
        if(!$cuserAgent){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $phone = $request['phone_number'] ?? $cuserAgent['phone_number'];
        $cuserAgent['password'] = md5($password);
        $cuserAgent['phone_number'] = $phone;
        $cuserAgent->save();

        return ReponseData::reponseFormat(200,'修改成功');
    }

    public function Frozen($request)
    {
        $id = $request['id'] ?? null;
        $frozen = $request['frozen'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $cuserAgent = CuserAgent::select('*')->where('id', $id)->first();
        if(!$cuserAgent){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($frozen === null){
            return ReponseData::reponseFormat(2001,'冻结状态必传!');
        }
        if($frozen != 1){
            $frozen = 0;
        }
        $cuserAgent->is_frozen = $frozen;
        $cuserAgent->save();
        return ReponseData::reponseFormat(200,'操作成功');
    }

    public function takeDown($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $cuserAgent = CuserAgent::select('*')->where('id', $id)->first();
        if(!$cuserAgent){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($type == 1){
            $cuserAgent->support_status = 1;
        }
        if($type == 2){
            $cuserAgent->support_status = 0;
        }

        $cuserAgent->save();
        return ReponseData::reponseFormat(200,'下架成功');
    }

    public function venueTakeDown($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        if(!$type){
            return ReponseData::reponseFormat(2000,'type必传!');
        }
        $agentVenue = AgentVenue::select('*')->where('id', $id)->first();
        if(!$agentVenue){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        if($type == 1){
            $agentVenue->support_status = 1;
        }
        if($type == 2){
            $agentVenue->support_status = 0;
        }

        $agentVenue->save();
        return ReponseData::reponseFormat(200,'下架成功');
    }

    public function updateYesterdayTurnover($request)
    {
        $id = $request['id'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $cuserAgent = CuserAgent::select('*')->where('id', $id)->first();
        if(!$cuserAgent){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $start_time = strtotime(date('Y-m-d', strtotime('-1 day')).' 00:00:00');
        $end_time = strtotime(date('Y-m-d', strtotime('-1 day')).' 23:59:59');

        $amount = AgentWalletLog::whereBetween('time', [$start_time, $end_time])->sum('amount');
        $cuserAgent['yesterday_turnover'] = $amount;
        $cuserAgent->save();

        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function venueList($request)
    {

        $venue_name = $request['venue_name'] ?? null;
        $agent_id = $request['agent_id'] ?? null;
        $vehicle_id = $request['labels'] ?? null;

        $page = $request['page'] ?? 1;
        $size = $request['size'] ?? 10;

        $query = AgentVenue::select('id','agent_id',
            'venue_name',
            'agent_name',
            'venue_introduction',
            'start_time',
            'end_time',
            'vehicle_id',
            'deposit',
            'support_status',
            'created_at',
            );
        if($venue_name){
            $query->where('venue_name', 'like', '%'.$venue_name.'%');

        }
        if($agent_id){
            $query = $query->where('agent_id',$agent_id);
        }
        if($vehicle_id){
            $query = $query->where('vehicle_id',$vehicle_id);
        }


        $rows = $query->orderBy("id", 'desc')->paginate($size, ['*'], 'page', $page);
        $special_area = array_column($rows->items(), 'agent_id');
        $specialNames = CuserAgent::whereIn('id',$special_area)->pluck('agent_name','id');
        foreach ($rows as $value){
            $value['start_time'] = date('H:i',$value['start_time']);
            $value['end_time'] = date('H:i',$value['end_time']);
            $value['type'] = $value['vehicle_id'];
            $value['type_name'] = $this->TypeValue[$value['type']] ?? '';
            unset($value['vehicle_id']);
            $vehicles_number = Vehicle::query()->where('agent_id', $value['agent_id'])->count();
            $number = Vehicle::query()->where('agent_id', $value['agent_id'])->where('vehicle_state',1)->count();
            $value['vehicles_number'] = $vehicles_number;
            $value['online_vehicle_number'] = $number;
            $value['agent_name'] = $specialNames[$value['agent_id']] ?? '';
        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function delete($request)
    {
        $id = $request['id'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必须传');
        }
        $vehicle = Vehicle::where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2001,'未找到该车辆');
        }
        $vehicle->delete();

        return ReponseData::reponseFormat(200,'删除成功!');
    }

    public function agentDelete($request)
    {
        $id = $request['id'] ?? null;
        if(!$id) {
            return ReponseData::reponseFormat(2000, 'id必传!');
        }
        $user = CuserAgent::select('*')->where('id', $id)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $key = 'agent_token_'.$id; //删除代理商账号同时干掉token
        Redis::del($key);
        $user->is_delete = 1;
        $user->save();
        return ReponseData::reponseFormat(200,'删除成功');
    }

    public function venueDelete($request)
    {
        $id = $request['id'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必须传');
        }
        $agentVenue = AgentVenue::where('id', $id)->first();
        if(!$agentVenue){
            return ReponseData::reponseFormat(2001,'未找到该车辆');
        }
        $agentVenue->delete();

        return ReponseData::reponseFormat(200,'删除成功!');
    }

    public function venueChangeSort($request)
    {
        $id = $request['id'] ?? null;
        $sort = $request['sort'] ?? null;
        if(!$sort){
            return ReponseData::reponseFormat(2000,'排序号必须传');
        }
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必须传');
        }
        $agentVenue = AgentVenue::where('id', $id)->first();
        if(!$agentVenue){
            return ReponseData::reponseFormat(2001,'未找到该车辆');
        }
        $agentVenue->sorting = $request['sort'];
        $agentVenue->save();

        return ReponseData::reponseFormat(200,'修改成功!');

    }


    public function venueVehicleList($request)
    {
        $data = [
            'venue_id' => $request['id'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
            'name' => $request['name'] ?? null,
        ];

        if(!$data['venue_id']){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $exists = AgentVenue::where('id', $data['venue_id'])->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该场地!');
        }

        $list = Vehicle::select('id','vehicle_name','vehicle_image','vehicle_state','vehicle_battery','top_speed','status','created_at')->where(['venue_id'=>$data['venue_id']]);
        if($data['name']){
            $list->where('vehicle_name', 'like', '%'.$data['name'].'%');
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        $Vehicle_ids = array_column($rows->items(), 'id');
        $vehicleConfig = VehicleConfig::query()
            ->whereIn('vehicle_id', $Vehicle_ids)
            ->pluck('video_definition', 'vehicle_id')
            ->toArray();
        foreach ($rows as $value){
            $value['video_definition'] = explode(',',$vehicleConfig[$value['id'] ?? '0']);//清晰度

        }
        return ReponseData::reponsePaginationFormat($rows);
    }


    public function agentWithdraw($request)
    {
        $data = [
            'agent_id' => $request['agent_id'] ?? null,
            'amount' => $request['amount'] ?? null,
            'bank_card' => $request['bank_card'] ?? null,
            'bank_name' => $request['bank_name'] ?? null,
            'real_name' => $request['real_name'] ?? null,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2000,'代理商id必传');
        }

        if(!$data['amount']){
            return ReponseData::reponseFormat(2000,'提现金额必传');
        }

        if(!$data['bank_card']){
            return ReponseData::reponseFormat(2000,'银行卡号必传');
        }
        if(!$data['bank_name']){
            return ReponseData::reponseFormat(2000,'开户行必填');
        }
        if(!$data['real_name']){
            return ReponseData::reponseFormat(2000,'真实姓名必须填');
        }

        $agent = CuserAgent::where('id', $data['agent_id'])->first();
        if(!$agent){
            return ReponseData::reponseFormat(2000,'未找到该用户哦');
        }
        $wallet = AgentWallet::where('agent_id', $data['agent_id'])->first();
        $balance = $wallet['balance'];
        if($wallet['balance'] < $data['amount']){
            return ReponseData::reponseFormat(2000,'可提现金额不足哦');
        }
        $wallet['balance'] = $wallet['balance'] - $data['amount'];
        $wallet->save();
        AgentWalletLog::create([
            'agent_id' => $data['agent_id'],
            'type'=>2,
            'type_name'=>'提现',
            'amount'=>$data['amount'],
            'balance'=>$wallet['balance'],
            'time'=>time(),
        ]);
        AgentWithdrawLog::create([
            'agent_id'=>$data['agent_id'],
            'agent_name'=>$agent['agent_name'],
            'withdraw_type'=>3,
            'withdraw_amount'=>$data['amount'],
            'balance'=>$wallet['balance'],
            'status'=>0,
            'enrolment_time'=>time(),
            'withdraw_name'=>$data['real_name'],
            'bank'=>$data['bank_name'],
            'bank_number'=>$data['bank_card'],
        ]);

        return ReponseData::reponseFormat(200,'提交成功');
    }

    public function setAgentWalletPassword($request)
    {
        $data = [
            'agent_id' => $request['agent_id'] ?? null,
            'password' => $request['password'] ?? null,
            'type' => $request['type'] ?? 1,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2000,'代理商id必传');
        }
        if($data['password'] === null){
            return ReponseData::reponseFormat(2000,'密码必传');

        }
//        if(!$data['type']){
//            return ReponseData::reponseFormat(2000,'类型必传');
//
//        }


        $agent = CuserAgent::where('id', $data['agent_id'])->first();

        if(!$agent) {
            return ReponseData::reponseFormat(2000, '未找到该账号');
        }

        if($data['type'] == 1){
            $agent->wallet_password = $data['password'];
            $agent->save();
            $message = '设置密码成功';

        }else{
            $agent->wallet_password = '';
            $agent->save();
            $message = '删除密码成功';
        }


        return ReponseData::reponseFormat(200,$message);
    }

    public function checkAgentWalletPassword($request)
    {
        $data = [
            'agent_id' => $request['agent_id'] ?? null,
            'password' => $request['password'] ?? null,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2000,'代理商id必传');
        }
        if($data['password'] === null){
            return ReponseData::reponseFormat(2000,'密码必传');

        }

        $agent = CuserAgent::where('id', $data['agent_id'])->first();

        if(!$agent) {
            return ReponseData::reponseFormat(2000, '未找到该账号');
        }

        if($agent['wallet_password'] != $data['password']){
            return ReponseData::reponseFormat(2000,'密码错误');
        }

        return ReponseData::reponseFormat(200,'验证成功');
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
        $user = CuserAgent::where('id', $id)->first();

        if (!$user) {
            return ReponseData::reponseFormat(2000, '未找到该账号!');

        }
        $balance = AgentWallet::where(['agent_id' => $id])->first();
        $afterBalance = $balance['balance'] + $amount;

        if($amount < 0){
            if($balance['balance'] < abs($amount)){
                return ReponseData::reponseFormat(2000,'余额不能减为负数');
            }
        }
        try {
            $updateAmount = $amount;
            $affected = $balance->update(['balance' => DB::raw("balance+{$updateAmount}")]);
            if ($affected != 1) {
                Log::info("后台修改余额： {$amount}, 增加失败： {$balance['balance']}");
            }
            AgentWalletLog::create([
                'agent_id' => $id,
                'type' => 2,
                'type_name' => '后台修改余额',
                'amount' => abs($amount),
                'balance' => $afterBalance,
                'time' => time(),
            ]);
        }catch (\Exception $e){
            return ReponseData::reponseFormat(2000,$e->getMessage());
        }

        return ReponseData::reponseFormat(200,'修改成功');
    }

    public function agentWithdrawLog($request)
    {
        $agent_id = $request['agent_id'] ?? null;

        $data = [
            'size'=> $request['size'] ?? 10,
            'page'=> $request['page'] ?? 1,
        ];
        if(!$agent_id){
            return ReponseData::reponseFormat(2000,'代理商id必传');
        }

        $agent = CuserAgent::where('id', $agent_id)->first();

        if(!$agent) {
            return ReponseData::reponseFormat(2000,'未找到代理商');

        }

        $list = AgentWithdrawLog::where('agent_id', $agent_id);

        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);

        foreach ($rows as $item) {
            $item['enrolment_time'] = date('Y-m-d H:i:s',$item['enrolment_time']);
            $item['audit_time'] = date('Y-m-d H:i:s',$item['audit_time']);
            $item['agent_name'] = $agent['agent_name'];
            $item['bank'] = '';
            $item['withdraw_name'] = '';
            $item['withdraw_account'] = '';
            $item['bank_number'] = '';
        }

        return  ReponseData::reponsePaginationFormat($rows);

    }
}







