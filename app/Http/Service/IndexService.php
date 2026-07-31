<?php

namespace App\Http\Service;


use App\Http\Service\AlipayNativeService;
use App\Models\AgentVenue;
use App\Models\AgentWallet;
use App\Models\AgentWalletLog;
use App\Models\AppVersion;
use App\Models\Banner;
use App\Models\CommonProblem;
use App\Models\ComplainRecord;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserEnergyLog;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Models\DepositActivity;
use App\Models\DepositLog;
use App\Models\DrivingRecord;
use App\Models\FeedBack;
use App\Models\ProtocolManage;
use App\Models\ReponseData;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use WeChatPay\Crypto\AesGcm;


class IndexService{
    protected $setvice;
    protected $labels = [
        1 => '遥控车',
        2 => '遥控船',
        3 => '工程车'
    ];
    public function __construct()
    {
        $this->setvice = new LoginService();
    }
    public function startupPage($request)
    {
        $url = '';
        $resp = [
            'url' =>$url
        ];
        return ReponseData::reponseFormatList(200,'成功',$resp);
    }

    public function index($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $uid = $request['uid'] ?? null;
        $type = $request['type'] ?? 0;
        $labels_id = $request['labels_id'] ?? 0;
        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::where('id',$uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该用户哦!');
        }
        $cuserAgentId = CuserAgent::where('superior_agent_id',$user['special_area'])->pluck('id');
        $cuserAgentId[] = $user['special_area'];
        if($type != 0){
            $venueList = AgentVenue::select('id','venue_name','venue_image','vehicle_id','labels','label_id')
                ->whereIn('agent_id',$cuserAgentId)
                ->where('label_id',$type)->where('support_status',1)
                ->withCount(['vehicles as online_vehicle_count' => function ($query) {
                    $query->whereIn('vehicle_state', [1,2]);
                }])
                ->withSum(['drivingRecords as total_amount' => function ($query) {
                    $query->where('reservation_status', 4)
                    ->where('order_time', '>=', Carbon::now()->subDay()->timestamp);
                }], 'payment_amount')
                ->withCount(['vehicles as driving_vehicle_count' => function ($query) {
                    $query->whereIn('vehicle_state', [2]);
                }])
                ->orderBy('driving_vehicle_count', 'desc')
                ->orderBy('online_vehicle_count', 'desc')
                ->orderBy('total_amount', 'desc')
                ->get();
        }else{
            $venueList = AgentVenue::select('id','venue_name','venue_image','vehicle_id','labels','label_id')
                ->whereIn('agent_id',$cuserAgentId)
                ->where('support_status',1)
                ->withCount(['vehicles as online_vehicle_count' => function ($query) {
                    $query->whereIn('vehicle_state', [1,2]);
                }])
                ->withSum(['drivingRecords as total_amount' => function ($query) {
                $query->where('reservation_status', 4)
                    ->where('order_time', '>=', Carbon::now()->subDay()->timestamp);
            }], 'payment_amount')
                ->withCount(['vehicles as driving_vehicle_count' => function ($query) {
                    $query->whereIn('vehicle_state', [2]);
                }])
                ->orderBy('driving_vehicle_count', 'desc')
                ->orderBy('online_vehicle_count', 'desc')
                ->orderBy('total_amount', 'desc')
                ->get();
        }
        $redisKey = $user['special_area'].'_type_'.$type;
        $redis = Redis::get($redisKey);
        if(!$redis) {
            if($venueList->isEmpty()){
                $venueList = [];
            }else{
                foreach ($venueList as $value) {
                    $online = Vehicle::where('venue_id', $value['id'])->whereIn('vehicle_state',[1,2])->where('status',1)->count();
                    $driving = Vehicle::where('venue_id', $value['id'])->where('vehicle_state', 2)->where('status',1)->count();
                    $queue = DrivingRecord::where('venue_id', $value['id'])->where('reservation_status', 3)->count();
                    $value['online'] = $online;
                    $value['driving'] = $driving;
                    $value['queue'] = $queue;
                    $value['venue_image'] = explode(',', $value['venue_image']);
                }
                Redis::setex($redisKey,5,json_encode($venueList));
            }
        }else{
            $venueList = json_decode($redis,true);
        }


        $respData = [
            'banner' => '',
            'venueList' => $venueList,
        ];

        return ReponseData::reponseFormatList(200,'获取成功',$respData);
    }

    public function getTitle()
    {
        $title =[ //暂时写死
            [
                'id'=>1,
                'name'=>'遥控车',
            ],
            [
                'id'=>2,
                'name'=>'越野车',
            ],
            [
                'id'=>3,
                'name'=>'工程车',
            ],

        ];
        return  ReponseData::reponseFormatList(200,'成功',$title);
    }

    public function venueDetail($request){
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'venue_id' => $request['venue_id'] ?? null,
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2001,'用户id必传!');
        }

        if(!$data['venue_id']){
            return ReponseData::reponseFormat(2001,'场地id必传!');
        }
        $user = Cuser::where('id', $data['uid'])->exists();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }

        $list = AgentVenue::select('id','agent_id','venue_name','venue_image','venue_introduction','labels','start_time','end_time','venue_config')->where(['id'=>$data['venue_id']])->first();
        if(!$list){
            return  ReponseData::reponseFormat(2000,'场地不存在');
        }
        $online = Vehicle::where(['agent_id'=>$list['agent_id'],'venue_id'=>$data['venue_id'],'status'=>1])->whereIn('vehicle_state',[1,2])->count(); //在线车辆
        $drive = Vehicle::where(['agent_id'=>$list['agent_id'],'venue_id'=>$data['venue_id'],'vehicle_state'=>2,'status'=>1])->count(); //驾驶中车辆
        $people_number = DrivingRecord::where('venue_id', $data['venue_id'])->where('reservation_status',3)->count();//表未建立 暂定
        $list['online'] = $online;
        $list['drive'] = $drive;
        $list['queue'] = $people_number;
        $list['start_time'] = date('H:i',$list['start_time']);
        $list['end_time'] = date('H:i',$list['end_time']);
        $vehicle = Vehicle::select('id','vehicle_name','vehicle_introduction','top_speed','vehicle_image','vehicle_state','is_password','vehicle_battery','password','app_transmitter_id','status','vehicle_sorting')->where(['agent_id'=>$list['agent_id'],'venue_id'=>$list['id'],'status'=>1])->where('vehicle_state','!=',0)->orderBy('vehicle_sorting', 'asc')->get(); //车辆列表
        if(!$vehicle->isEmpty()){
            foreach($vehicle as $value){
                $vehicle_people_number = DrivingRecord::where('vehicle_id', $value['id'])->where('reservation_status', 3)->count();//表未建立 暂定
                $value['vehicle_queue'] = $vehicle_people_number ?? 0;
            }
        }

        $list['venue_config'] = json_decode($list['venue_config'],true);
        $list['venue_image'] = explode(',',$list['venue_image']);
        $list['vehicle'] = $vehicle;
        $list['content_url'] = env('CONTENT_URL','xhzzf.huazyk.cn') ;
        $list['content_url_port'] = env('CONTENT_URL_PORT','8899') ;
        $list['web_camera_host'] = env('WEB_CAMERA_HOST','') ;
        $list['web_camera_port'] = env('WEB_CAMERA_PORT','') ;
        $list['web_camera_user_name'] = env('WEB_CAMERA_NAME','') ;
        $list['web_camera_user_password'] = env('WEB_CAMERA_PASSWORD','') ;

        return ReponseData::reponseFormatList(200,'成功',$list);
    }

    public function mine($request)
    {
//        $request = $this->setvice->decrypt($request['data']);

        $uid = $request['uid'] ?? null;
        if(!$uid){
            return ReponseData::reponseFormat(2001,'用户id必传!');
        }

        $user = Cuser::select('id','username','special_area','head_shot','show_id','phone_number','is_screenshot')->where('id', $uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $wallet = CuserWallet::getBalance($uid,$user['special_area']);
        $resp = [
            'id'=>$user['id'],
            'head_shot'=>$user['head_shot'],
            'username' => $user['username'],
            'wallet' => $wallet,
            'show_id' => $user['show_id'],
            'phone_number' => $user['phone_number'],
            'wechat_service_url' => env('WECHAT_SERVICE_URL','') ,
            'wx_corp_id' => env('WX_CORP_ID',''),
            'is_screenshot'=>$user['is_screenshot'],
            'business_wechat' => env('BUSINESS_WECHAT','we1731747901') ,
            'business_email' => env('BUSINESS_EMAIL','fuhuanyong@sqzskj.cn') ,
        ];

        return  ReponseData::reponseFormatList(200,'成功',$resp);
    }

    public function specialList($request)
    {
//        $request = $this->setvice->decrypt($request['data']);

        $uid = $request['uid'] ?? null;
        Log::info('request_'.$uid);
        $type = $request['type'] ?? null;

        if(!$uid){
            return ReponseData::reponseFormat(2001,'用户id必传!');
        }

        $user = Cuser::select('id','username','head_shot','phone_number')->where('id', $uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }

        if(isset($type) && $type == 2){
            $specialList = CuserAgent::select('id','agent_name','head_shot','type','sorting')
                ->where('id','>',1)
                ->whereIn('type',[2,3])
                ->where('superior_agent_id',0)
                ->orderBy('sorting', 'asc')
                ->orderBy('weekly_amount', 'desc')->get();
        }else{
            $specialList = CuserAgent::select('id','agent_name','head_shot','type','sorting')
                ->where('id','>',1)
                ->whereIn('type',[1,3])
                ->where('superior_agent_id',0)
                ->orderBy('sorting', 'asc')
                ->orderBy('weekly_amount', 'desc')
                ->get();
        }

        if($user['phone_number'] == 18168526602){
            $specialList = CuserAgent::select('id','agent_name','head_shot','type')->where('id',1)->get();
        }
        $sid = $specialList->pluck('id');
        $amountArray = CuserWallet::where('uid',$uid)->whereIn('type',$sid)->pluck('balance','type')->toArray();
        $newSpecialList = [];
        foreach ($specialList as $value) {
            $value['partitions_number'] = CuserAgent::where('superior_agent_id', $value['id'])->count();
            $cuserAgentId = CuserAgent::where('superior_agent_id',$value['id'])->pluck('id');
            $cuserAgentId->push($value['id']);
            $venueId = AgentVenue::whereIn('agent_id',$cuserAgentId)->where('support_status',1)->pluck('id');
            $value['vehicles_number'] = Vehicle::whereIn('agent_id',$cuserAgentId)->whereIn('venue_id',$venueId)->whereIn('vehicle_state',[1,2])->where('status',1)->count();
            $value['balance'] = $amountArray[$value['id']] ?? 0;
            $value['image'] = $value['head_shot'] ?? '';
            unset($value['head_shot']);
            $newSpecialList[] = $value;
        }

        return  ReponseData::reponseFormatList(200,'成功',$newSpecialList);
    }

    public function changeSpecial($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $uid = $request['uid'] ?? null;
        $specialId = $request['special_id'] ?? null;
        if(!$uid){
            return ReponseData::reponseFormat(2001,'用户id必传!');
        }

        $user = Cuser::select('id','username')->where('id', $uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        if(!$specialId){
            return ReponseData::reponseFormat(2000,'专区id必传');
        }
        $specialList = CuserAgent::where('id', $specialId)->exists();
        if(!$specialList){
            return ReponseData::reponseFormat(2001,'未找到该专区');
        }

        $user->special_area = $specialId;
        $user->save();

        return ReponseData::reponseFormat(200,'变更成功');

    }

    public function reservationList($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::select('id','username','special_area')->where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $query = DrivingRecord::select('*');
        $query->where('uid', $data['uid'])->where('special_area',$user['special_area']);

        $rows = $query->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        foreach ($rows as $row) {
            $exitTime = time() - $row['end_time'];
            if($row['reservation_status'] == 4 && $exitTime <= 43200){
                $row['is_reservation'] = 1;
            }else{
                $row['is_reservation'] = 0;
            }
            if($row['appeal_status'] == 1 || $row['appeal_status'] == 2){
                $row['is_reservation'] = 0;
            }
            $row['billing_rules'] = json_decode($row['billing_rules'],true);
            $row['app_transmitter_id'] = $row['transmitter_id'];
            unset($row['transmitter_id']);
        }
        return ReponseData::reponsePaginationFormat($rows);

    }

    public function drivingRecord($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::select('id','username','special_area','head_shot')->where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }

        $query = DrivingRecord::select('id','user_name','vehicle_name','vehicle_id','order_no','billing_method','venue_id','venue_name','payment_amount','appeal_status','reservation_status','order_time','start_time','end_time','payment_type','head_shot');
        $query->where('uid', $data['uid'])->where('reservation_status',4)->where('special_area',$user['special_area']);
        $rows = $query->orderBy("order_time", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        foreach ($rows as $row) {
            $row['user_name'] = $user['username'];
            $row['head_shot'] = $user['head_shot'];
        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function walletList($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
            'type' => $request['type'] ?? null,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::select('id','username','special_area')->where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $query = CuserWalletLog::select('id','type','time','amount','type_name');
        $query->where('uid', $data['uid'])->where('special_area',$user['special_area']);
        if($data['type']){
            $query->where('type',$data['type']);
        }
        $rows = $query->orderBy("time", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        foreach ($rows as $value) {
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
        }

        return ReponseData::reponsePaginationFormat($rows);
    }

    public function wechatDeposit($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'amount' => $request['amount'] ?? null,
            'activity_id' => $request['activity_id'] ?? null,

        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户哦!');
        }
        $depositOrder = [
            'uid' => $request['uid'],
            'amount' => $request['amount'],
            'user_name' => $user['username'],
            'special_area'=> $user['special_area'],
            'special_area_name'=> $user['special_area_name'],
            'phone_number' => $user['phone_number'],
            'time' => time(),
            'type' => 0,
            'pay_type' => 1,//1微信，支付宝，3银行卡，4momo
            'order_no' => orderNo('WECHAT'),
        ];
        if($data['activity_id']){
            $activity = DepositActivity::where('activity_id', $data['activity_id'])->first();
            $num = DepositLog::where('activity_id', $data['activity_id'])->where('uid',$data['uid'])->count();

            if($activity){
                if($num < $activity['deposit_amount']){
                    return ReponseData::reponseFormat(2000,'充值失败，活动参与已达上限请选择其他套餐哦');
                }
                $depositOrder['activity_id'] = $data['activity_id'];
                $depositOrder['sendMoney'] = $activity['send_energy'];
            }
        }
        DepositLog::create($depositOrder);

        try{
            $wechatpay = new WechatPayV3Service();
            $depositOrder['subject'] = '电池购买';
            $resp = $wechatpay->createAppOrder($depositOrder);
            return ReponseData::reponseFormatList(200,'下单成功',$resp);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return $e->getMessage();
        }


    }

    public function wechatNotify($request)
    {
        $inBody = $request->getContent();

        $bodyArray = json_decode($inBody, true);
        if (empty($bodyArray['resource'])) {
            return response()->json(['code' => 'FAIL', 'message' => '数据格式错误'], 400);
        }
        $resource = $bodyArray['resource'];

        try {
            $decrypted = AesGcm::decrypt(
                $resource['ciphertext'],
                config('wechat.apiv3_key'),
                $resource['nonce'],
                $resource['associated_data']
            );

            $payData = json_decode($decrypted, true);

            Log::info('微信支付回调解密成功: ', $payData);

            // 如果不是支付成功状态，直接抛弃
            if ($payData['trade_state'] !== 'SUCCESS') {
                return response()->json(['code' => 'SUCCESS', 'message' => '非成功状态不处理']);
            }

            $outTradeNo = $payData['out_trade_no'];
            $payAmount = $payData['amount']['total'] / 100;
            $tradeNo = $payData['transaction_id'];
            $order = DepositLog::where('order_no',$outTradeNo)->first();
            if(!$order){
                return response()->json(['code' => 'FAIL', 'message' => '处理失败'], 500);
            }

            if($order->type == 1 || $order->type == 2){
                Log::info('支付回调订单：'.$outTradeNo.'已完成，重复回调');
                return response()->json(['code' => 'SUCCESS', 'message' => '成功']);
            }
            $order->update([
                'finish_time' => time(),
                'type' => 1,
                'third_order_no' => $tradeNo,
            ]);
            WalletService::safeAdjust([
                'uid' => $order->uid,
                'type' => CuserWalletLog::TypeDeposit,
                'type_name'=>'充值',
                'make_order_no' => $order['order_no'],
                'amount' => $payAmount,
                'venue'  => $order->special_area_name,
                'special_area' => $order->special_area,
            ]);
            if($order->activity_id != ''){
                $sendMoney = $order->sendMoney;
                WalletService::safeAdjustEnergy(
                    [
                        'uid' => $order->uid,
                        'type' => CuserEnergyLog::TypeDeposit,
                        'type_name'=>'充值赠送',
                        'make_order_no' => $order['order_no'],
                        'amount' => $sendMoney,
                        'venue'  => $order->special_area_name,
                        'special_area' => $order->special_area,
                        'activity_id' => $order->activity_id,

                    ]
                );
            }
            return response()->json(['code' => 'SUCCESS', 'message' => '成功']);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['code' => 'FAIL', 'message' => '处理失败'], 500);
        }

    }

    public function alipayDeposit($request)
    {
        $data = [
            'uid' => $request['uid'] ?? null,
            'amount' => $request['amount'] ?? null,
            'activity_id' => $request['activity_id'] ?? null,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }
        $user = Cuser::where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到该用户哦!');
        }
        $depositOrder = [
            'uid' => $request['uid'],
            'amount' => $request['amount'],
            'user_name' => $user['username'],
            'special_area'=> $user['special_area'],
            'special_area_name'=> $user['special_area_name'],
            'phone_number' => $user['phone_number'],
            'time' => time(),
            'type' => 0,
            'pay_type' => 2,//1微信，支付宝，3银行卡，4momo
            'order_no' => orderNo('ALIPAY'),
        ];
        if($data['activity_id']){
            $activity = DepositActivity::where('activity_id', $data['activity_id'])->first();
            $num = DepositLog::where('activity_id', $data['activity_id'])->where('type',1)->where('uid',$data['uid'])->count();

            if($activity){
                if($num < $activity['deposit_amount']){
                  return ReponseData::reponseFormat(2000,'充值失败，活动参与已达上限请选择其他套餐哦');
                }
                $depositOrder['activity_id'] = $data['activity_id'];
                $depositOrder['sendMoney'] = $activity['send_energy'];
            }
        }
        DepositLog::create($depositOrder);

        try {
            // 2. 初始化原生支付宝工具类
            $alipay = new AlipayNativeService();
            $depositOrder['subject'] = '电池购买';
            // 3. 生成APP支付的orderStr
            $orderStr = $alipay->createAppOrder($depositOrder);

            // 4. 可选：记录订单到数据库（示例）
            // \App\Models\Order::updateOrCreate(
            //     ['out_trade_no' => $validated['out_trade_no']],
            //     [
            //         'total_amount' => $validated['total_amount'],
            //         'subject' => $validated['subject'],
            //         'status' => 'unpaid',
            //         'created_at' => now(),
            //     ]
            // );

            // 5. 返回给APP端
            return response()->json([
                'code' => 200,
                'msg' => '生成订单成功',
                'data' => ['order_str' => $orderStr]
            ]);
        } catch (\Exception $e) {
            Log::error('原生支付宝生成支付参数失败：'.$e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '生成支付参数失败：'.$e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function alipayNotify($request)
    {
        $params = $request->all();
        Log::info('alipayNotify: '.json_encode($params));
//        try {
            // 2. 初始化工具类并验签（关键：防止伪造通知）
            $alipay = new AlipayNativeService();
//            if (!$alipay->verifySign($params)) {
//                Log::error('支付宝异步通知验签失败');
//                return 'fail'; // 验签失败，返回fail
//            }

            // 3. 验证交易状态（TRADE_SUCCESS=支付成功）
            if ($params['trade_status'] != 'TRADE_SUCCESS') {
                Log::warning('支付宝交易状态异常：'.$params['trade_status']);
                return 'success'; // 状态异常但验签成功，仍返回success避免重复通知
            }

            // 4. 处理核心业务逻辑（更新订单状态）
            $outTradeNo = $params['out_trade_no']; // 商户订单号
            $tradeNo = $params['trade_no']; // 支付宝交易号
            $payAmount = $params['total_amount']; // 实际支付金额
            $order = DepositLog::where('order_no',$outTradeNo)->first();
            if(!$order){
                return 'fail';
            }

            if($order->type == 1 || $order->type == 2){
                Log::info('支付回调订单：'.$outTradeNo.'已完成，重复回调');
                return 'success';
            }
            $order->update([
                'finish_time' => time(),
                'type' => 1,
                'third_order_no' => $tradeNo,
            ]);
            WalletService::safeAdjust([
                'uid' => $order->uid,
                'type' => CuserWalletLog::TypeDeposit,
                'type_name'=>'充值',
                'make_order_no' => $order['order_no'],
                'amount' => $payAmount,
                'venue'  => $order->special_area_name,
                'special_area' => $order->special_area,
            ]);
            if($order->activity_id != ''){
                $sendMoney = $order->sendMoney;
                WalletService::safeAdjustEnergy(
                    [
                        'uid' => $order->uid,
                        'type' => CuserEnergyLog::TypeDeposit,
                        'type_name'=>'充值赠送',
                        'make_order_no' => $order['order_no'],
                        'amount' => $sendMoney,
                        'venue'  => $order->special_area_name,
                        'special_area' => $order->special_area,

                    ]
                );
            }

            // 示例：更新订单状态
            // $order = \App\Models\Order::where('out_trade_no', $outTradeNo)->first();
            // if ($order && $order->status == 'unpaid') {
            //     $order->update([
            //         'status' => 'paid',
            //         'alipay_trade_no' => $tradeNo,
            //         'paid_at' => date('Y-m-d H:i:s', strtotime($params['gmt_payment'])),
            //         'updated_at' => now(),
            //     ]);
            // }

            // 5. 必须返回"success"，否则支付宝会重复通知（最多8次）
            return 'success';
//        } catch (\Exception $e) {
//            Log::error('支付宝异步通知处理失败：'.$e->getMessage());
//            return 'fail';
//        }
    }

    public function feedBack($request)
    {
//        $request = $this->decrypt($request['data']);
        $image = $request['image'] ?? null;
        $content = $request['content'] ?? null;
        $uid = $request['uid'] ?? null;
        $agent_id = $request['agent_id'] ?? null;

        if(!$content){
            return ReponseData::reponseFormat(2002,'意见必填');
        }
        $data = [];

        if($uid){
            $user = Cuser::where('id', $uid)->first();
            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该账号!');
            }
            $data['uid'] = $uid;
            $data['phone'] = $user['phone_number'];
            $data['user_name'] = $user['username'];

        }

        if($agent_id){
            $agent = CuserAgent::where('id',$agent_id)->first();
            if(!$agent){
                return ReponseData::reponseFormat(2000,'未找到该代理商账号!');
            }
            $data['agent_id'] = $agent_id;
            $data['phone'] = $agent['phone_number'];
            $data['user_name'] = $user['agent_name'];
        }
        $data['image'] = $image;
        $data['content'] = $content;
        $data['time'] = time();
        FeedBack::create($data);

        return ReponseData::reponseFormat(200,'提交成功');
    }

    public function deactivate($request)
    {
//        $data = $this->decrypt($request['data']);

        $data = [
            'uid' => $request['uid'] ?? null,
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::select('id','username')->where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $user->is_cancel = 1;

        return ReponseData::reponseFormat(200,'注销成功!');
    }

    public function drivingProtocol($request)
    {
//        $data = $this->decrypt($request['data']);
         $uid = $request['uid'] ?? null;

         if(!$uid){
             return ReponseData::reponseFormat(2000,'用户id必传');
         }

         $list = ProtocolManage::where('type',1)->first();

         return ReponseData::reponseFormatList(200,'成功',$list);
    }

    public function complainList($request)
    {
//        $request = $this->decrypt($request['data']);

        $data = [
            'uid' => $request['uid'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::select('id','username','special_area')->where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $query = ComplainRecord::select('id','uid', 'order_no','venue_id', 'venue_name', 'billing_method','appeal_status','time','platform_reply');
        $query->where('uid', $data['uid']);

        $rows = $query->orderBy("time", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);

        return  ReponseData::reponsePaginationFormat($rows);
    }


    public function changeName($request)
    {
//        $request = $this->decrypt($request['data']);

        $uid = $request['uid'] ?? null;
        $name = $request['name'] ?? null;

        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        if(!$name){
            return ReponseData::reponseFormat(2000,'昵称必传!');
        }
        $user = Cuser::select('id','username','special_area')->where('id', $uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $user->username = $name;
        $user->save();

        return ReponseData::reponseFormat(200,'成功');
    }

    public function accountCancel($request)
    {
//        $request = $this->decrypt($request['data']);

        $uid = $request['uid'] ?? null;

        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }

        $user = Cuser::select('*')->where('id', $uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }
        $user->update(['is_cancel'=>1]);

        return ReponseData::reponseFormat(200,'注销成功');
    }


    public function complain($request)
    {
//        $request = $this->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'content' => $request['content'] ?? null,
            'image' => $request['image'] ?? '',
            'order_no' =>  $request['order_no'] ?? null,
        ];

        $key = 'complain_'.$data['order_no'].'_'.$data['uid'];
        $ret = Redis::set($key, '1','ex','5','nx');
        if(!$ret){
            return ReponseData::reponseFormat(2000,'请勿重复点击哦');
        }
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }
        if(!$data['content']){
            return ReponseData::reponseFormat(2000,'内容必传');
        }
//        if(!$data['image']){
//            return ReponseData::reponseFormat(2000,'图片必传');
//        }

        $order = DrivingRecord::where(['order_no'=>$data['order_no'],'reservation_status'=>4])->first();
        if(!$order){
            return ReponseData::reponseFormat(2000,'未找到该订单，请确认是否已完成');
        }
        if($order['payment_type'] == 2){
            return  ReponseData::reponseFormat(2000,'能量消费不可以申诉,请联系客服');
        }

        if($order['payment_amount'] <= 0){
            return ReponseData::reponseFormat(2000,'消费金额为0,不能申诉哦');
        }
        if($order['appeal_status'] == 2){
            return ReponseData::reponseFormat(2000,'已经申诉过了哦');
        }
        $data['user_name'] = $order['user_name'];
        $data['phone'] = $order['phone'];
        $data['venue_id'] = $order['venue_id'];
        $data['venue_name'] = $order['venue_name'];
        $data['vehicle_id'] = $order['vehicle_id'];
        $data['vehicle_name'] = $order['vehicle_name'];
        $data['reservation_status'] = $order['reservation_status'];
        $data['billing_method'] = $order['billing_method'];
        $data['appeal_status'] = 1;
        $data['amount'] = $order['payment_amount'];
        $data['payment_type'] = $order['payment_type'];
        $data['refund_cause'] = $data['content'];
        $data['time'] = time();
        ComplainRecord::create($data);
        $order->appeal_status = 1;
        $order->save();

        return ReponseData::reponseFormat(200,'成功');
    }


    public function startDriving($request)
    {
//        $request = $this->decrypt($request['data']);
        $request['platform'] = $request->header('platform') ?? '';
        $request['versionCode'] = $request->header('versionCode') ?? '';
        $data = [
            'uid' => $request['uid'] ?? null,
            'agent_id' => $request['agent_id'] ?? null,
//            'transmitter_id' => $request['transmitter_id'] ?? null,
//            'receiver_id' => $request['receiver_id'] ?? null,
            'type' => $request['type'] ?? null,
//            'amount' => $request['amount'] ?? null,
            'order_no' => $request['order_no'] ?? null,
//            'payment_type' => $request['payment_type'] ?? null,
//            'billing_method' => $request['billing_method'] ?? null,
        ];
//        if(!$data['transmitter_id']){
//            return ReponseData::reponseFormat(2000,'发射机id必传');
//        }
//        if(!$data['receiver_id']){
//            return ReponseData::reponseFormat(2000,'接收机id必传');
//        }

        //用户端处理逻辑
//        Redis::set($data['transmitter_id'],$data['receiver_id']); //绑定车辆接收机、发射机id

        if($data['uid']){
            if(!$data['order_no']){
                return ReponseData::reponseFormat(2000,'订单号必传');
            }

            if(!$data['type']){
                return ReponseData::reponseFormat(2000,'驾驶状态必传');
            }

//            if(!$data['amount']){
//                return ReponseData::reponseFormat(2000,'金额必传');
//            }

//            if($data['payment_type'] === null){
//                return ReponseData::reponseFormat(2000,'支付类型必传');
//            }
//            if($data['billing_method'] === null){
//                return ReponseData::reponseFormat(2000,'计费方式必传');
//            }
            $user = Cuser::where('id',$data['uid'])->first();
            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该用户');
            }
            $order = DrivingRecord::where('order_no',$data['order_no'])->first();
            if(!$order){
                return ReponseData::reponseFormat(2000,'未找到该预约单号');
            }
            $vehicle = Vehicle::where('id',$order['vehicle_id'])->first();

            $receiverId = $vehicle['receiver_id'];
            Redis::set($order['transmitter_id'],$receiverId); //绑定车辆接收机、发射机id

            $data['receiver_id'] = $receiverId;
            $billingRules = json_decode($order['billing_rules'],true);
            if(!$billingRules || !$billingRules['battery']){
                return ReponseData::reponseFormat(2000,'驾驶数据错误');
            }
            $data['amount'] = $billingRules['battery'] ?? 0;
            $data['payment_type'] = $order['payment_type'];
            $data['billing_method'] = $order['billing_method'];

            $cuserWallet = CuserWallet::getBalance($data['uid'],$user['special_area']);

            if($data['type'] == 1){  //开始驾驶
                $key = 'start_driving_'.$data['order_no'].'_'.$data['uid'];
                $ret = Redis::set($key, '1','ex','1','nx');


                if(!$ret){
                    return ReponseData::reponseFormat(200,'请勿重复点击哦');
                }
                if($vehicle['is_agent_start'] == 1){
                    Log::info('车辆不在空闲中，车辆被代理商驾驶中！: '.$order['order_no']);

                    return  ReponseData::reponseFormat(2000,'车辆不在空闲中');
                }
                if($order['reservation_status'] == 4 || $order['reservation_status'] == 5){
                    return ReponseData::reponseFormat(2000,'订单已完成或已取消预约');
                }
                if($vehicle['status'] != 1){
                    return  ReponseData::reponseFormat(2001,'车辆被下架，暂时无法驾驶');
                }
                Redis::set('vehicle'.$vehicle['id'],'freeze'); //开始驾驶锁车
                if($vehicle['vehicle_state'] != 1){
                    Log::info('车辆不在空闲中  车辆状态不等于1，用户端：' .$order['order_no']);
                    return  ReponseData::reponseFormat(2002,'车辆不在空闲中' );
                }
                if($data['payment_type'] == 1){
                    if($cuserWallet['balance'] < $data['amount'] || $cuserWallet['balance'] <= 0){
                        $order->update([
                            'reservation_status' => 5,
                            'end_time'=>time(),
                            'transmitter_id' => '0',//释放发射机id
                        ]);
                        $vehicle->update(['vehicle_state' => 1]);

                        return ReponseData::reponseFormat(2000,'电池余额不足！请先充值哦');
                    }
                    WalletService::safeAdjust(
                        [
                            'uid' => $user['id'],
                            'type' => CuserWalletLog::TypeConsumption,
                            'type_name'=>'驾驶扣款',
                            'make_order_no' => $order['order_no'],
                            'amount' => $data['amount'] * -1,
                            'venue'  => $user->special_area_name,
                            'special_area' => $user->special_area,
                        ]
                    );
                    //代理商余额增加 待定
                    $order->update([
                            'reservation_status' => 3,
                            'payment_amount'=> $data['amount'],
                            'start_time'=>time(),
                      ]
                    );
                    $vehicle->update(['vehicle_state' => 2]);

                    $log = [
                        'order_no' => $order['order_no'] ?? '',
                        'amount' => $data['amount'] ?? '',
                        'platform' =>  $request['platform'],
                        'versionCode' =>  $request['versionCode'],
                    ];
                    //不管是按次还是按时间 都塞一份， 最终给按次用
                    $orderNo = $order['order_no'];
                    $key = 'driving_'.$orderNo;
                    Redis::setex($key,40,1);
                    Log::info("开始驾驶扣除金额 : " . json_encode($log, 320));
                    return  ReponseData::reponseFormat(200,'开始驾驶成功');
                }

                if($data['payment_type'] == 2){

                    if($cuserWallet['energy'] < $data['amount'] || $cuserWallet['energy'] <= 0){
                        $order->update([
                            'reservation_status' => 5,
                            'end_time'=>time(),
                            'transmitter_id' => '0',//释放发射机id
                        ]);
                        $vehicle->update(['vehicle_state' => 1]);
                        return ReponseData::reponseFormat(2000,'能量余额不足！请先充值哦');
                    }
                    WalletService::safeAdjustEnergy(
                        [
                            'uid' => $user['id'],
                            'type' => CuserEnergyLog::TypeConsumption,
                            'type_name'=>'驾驶扣款',
                            'make_order_no' => $order['order_no'],
                            'amount' => $data['amount'] * -1,
                            'venue'  => $user->special_area_name,
                            'special_area' =>$user->special_area,
                        ]
                    );
                    $order->update([
                            'reservation_status' => 3,
                            'payment_amount'=> $data['amount'],
                            'start_time'=>time(),
                        ]
                    );
                    $vehicle->update(['vehicle_state' => 2]);
                    //不管是按次还是按时间 都塞一份， 最终给按次用
                    $orderNo = $order['order_no'];
                    $key = 'driving_'.$orderNo;
                    Redis::setex($key,40,1);
                    return  ReponseData::reponseFormat(200,'开始驾驶成功');
                }

            }

            if($data['type'] == 2) { //继续驾驶
                if($order['reservation_status'] == 4 || $order['reservation_status'] == 5){
                    return ReponseData::reponseFormat(2000,'订单已完成或已取消预约');
                }
                if($vehicle['status'] != 1){
                    return  ReponseData::reponseFormat(2001,'车辆被下架，暂时无法驾驶');
                }


                $log = [
                    'order_no' => $order['order_no'] ?? '',
                    'amount' => $data['amount'] ?? 0,
                    'platform' =>  $request['platform'],
                    'versionCode' =>  $request['versionCode'],
                ];
                Log::info("继续驾驶扣除金额 : " . json_encode($log, 320));
                if($data['billing_method'] == 1){
                    $time = time();
                    $billing_rules = json_decode($order['billing_rules'],true);
                    if(!$billing_rules){
                        return ReponseData::reponseFormat(2000,'订单错误');
                    }
                    $rulesTime = $billing_rules['time'] * 60; //阶段总时间
                    $startTime = $order['start_time'] + $rulesTime; //阶段应结束时间
                    if($time > $startTime){
                        return ReponseData::reponseFormat(2000,'按次计费驾驶已结束哦！');
                    }
                    $orderNo = $order['order_no'];
                    $key = 'driving_'.$orderNo;
                    Redis::setex($key,40,1);
                    return ReponseData::reponseFormat(200,'按次计费继续驾驶成功！');
                }

                if ($data['payment_type'] == 1) {
                    if ($cuserWallet['balance'] < $data['amount']) {
                        $order->update([
                            'reservation_status' => 4,
                            'end_time'=>time(),
                            'transmitter_id' => '0',//释放发射机id
                        ]);
                        $vehicle->update(['vehicle_state' => 1]);

                        return ReponseData::reponseFormat(2003, '电池余额不足！请先充值哦');
                    }
                    $balanceAddAmount = $data['amount'] * -1;
                    $updateQuery = CuserWallet::where(['uid' => $data['uid']])->where('type',$user['special_area']);
                    $affected = $updateQuery->update(['balance' => DB::raw("balance+{$balanceAddAmount}")]);
                    if($affected != 1){
                        Log::info("继续驾驶金额： {$data['amount']}, 余额不足或扣款失败： {$cuserWallet['balance']}");
                        return ReponseData::reponseFormat(2003,'余额不足');
                    }
                    $walletLog =  CuserWalletLog::where('make_order_no',$data['order_no'])->first();
                    if(!$walletLog){
                        return ReponseData::reponseFormat(2000,'未找到该条记录');
                    }
                    $walletLog->update([
                        'amount'=> $walletLog['amount'] + $balanceAddAmount * -1,
                        'balance'=> $walletLog['balance'] - $balanceAddAmount * -1,
                    ]);
                    //代理商余额增加 待定
                    $order->update([
                            'payment_amount' =>$order['payment_amount'] + $data['amount'],
                        ]
                    );

                    return ReponseData::reponseFormat(200, '继续驾驶成功');
                }

                if ($data['payment_type'] == 2) {
                    if ($cuserWallet['energy'] < $data['amount']) {
                        $order->update([
                            'reservation_status' => 4,
                            'end_time'=>time(),
                            'transmitter_id' => '0',//释放发射机id
                        ]);
                        $vehicle->update(['vehicle_state' => 1]);

                        return ReponseData::reponseFormat(2004, '能量余额不足！请先充值哦');
                    }
                    $updateQuery = CuserWallet::where(['uid' => $data['uid']])->where('type',$user['special_area']);
                    $deduction = $data['amount'] * -1;
                    $affected = $updateQuery->update(['energy' => DB::raw("energy+{$deduction}")]);
                    if($affected != 1){
                        Log::info("继续驾驶金额： {$data['amount']}, 能量余额不足或扣款失败： {$cuserWallet['energy']}");
                        return ReponseData::reponseFormat(2003,'余额不足');
                    }
                    $walletLog =  CuserEnergyLog::where('make_order_no',$data['order_no'])->first();
                    if(!$walletLog){
                        return ReponseData::reponseFormat(2000,'未找到该条记录');
                    }
                    $walletLog->update([
                        'amount'=> $walletLog['energy'] + $data['amount'],
                        'balance'=> $walletLog['energy'] - $data['amount'],
                    ]);
                    //代理商余额增加 待定
                    $order->update([
                            'payment_amount' =>$order['payment_amount'] + $data['amount'],
                        ]
                    );
                    return ReponseData::reponseFormat(200, '继续驾驶成功');
                }
            }

            if($data['type'] == 3){ //结束驾驶
                $key = 'end_driving_'.$data['order_no'].'_'.$data['uid'];
                $ret = Redis::set($key, '1','ex','1','nx');
                if(!$ret){
                    return ReponseData::reponseFormat(200,'请勿重复点击哦');
                }
                if($order['reservation_status'] == 4 || $order['reservation_status'] == 5){
                    return  ReponseData::reponseFormat(2000,'退出驾驶成功');
                }

                if($order['start_time'] === 0){
                    $order->update([
                        'reservation_status' => 5,
                        'transmitter_id' => '0',//释放发射机id
                        'payment_amount' => 0,
                    ]);
                    return  ReponseData::reponseFormat(2000,'退出驾驶成功');

                }
                $time = time(); //当前时间

                Redis::del($order['transmitter_id']); //解绑绑定车辆接收机、发射机id

                $billing_rules = json_decode($order['billing_rules'],true);
                if(!$billing_rules){
                    return ReponseData::reponseFormat(2000,'订单错误');
                }
                $rulesAmount = $billing_rules['battery']; //阶段总金额
                $rulesTime = $billing_rules['time'] * 60; //阶段总时间
                $startTime = $order['start_time'];//开始时间戳
                $returnAmount = 0;
                //按时间只要开始驾驶 阶段的一半时间 就扣一半的钱
                if($order['billing_method'] != 1){ //提前结束驾驶
                    $count = intval(($time - $startTime) / $rulesTime) + 1; //已进行次数
                    $shouldTime = $startTime + ($rulesTime * $count); //当前阶段应该结束时间
                    $shouldTime2 = $shouldTime - $time; //阶段剩余多少时间
                    $shouldTime3 = $rulesTime - $shouldTime2; //阶段时间-剩余时间
                    $num = $shouldTime3 / $rulesTime;
                    if($num < 0.7){
//                        if($num < 0.3){
//                            $returnAmount = intval($rulesAmount * ($shouldTime2 / $rulesTime)); //返回金额 = 阶段金额*当前剩余时间/阶段时间
//                        }{
                        $returnAmount = intval($rulesAmount * ($shouldTime2 / $rulesTime)); //返回金额 = 阶段金额*当前剩余时间/阶段时间
//                        }
                        if($num >= 0.3 && $num < 0.5){
                            $returnAmount = intval($rulesAmount * 0.5);
                        }
                        if($returnAmount == $order['payment_amount'] && $count == 1){
                            $returnAmount = 0;
                        }

                        $totalAmount = ($billing_rules['battery'] * $count);
                        if($count > 1 &&  $totalAmount > $order['payment_amount']){ //继续驾驶没成功
                            $returnAmount = 0;
                        }

                        if($order['payment_type'] == 1){
                            WalletService::safeAdjust([
                                'uid' => $user->id,
                                'type' => CuserWalletLog::TypeReturn,
                                'type_name'=>'提前结束驾驶退还',
                                'make_order_no' => $order['order_no'],
                                'amount' => $returnAmount,
                                'venue'  => $user->special_area_name,
                                'special_area' => $user->special_area,
                            ]);
                        }
                        if($order['payment_type'] == 2){
                            WalletService::safeAdjustEnergy([
                                'uid' => $user->id,
                                'type' => CuserWalletLog::TypeReturn,
                                'type_name'=>'提前结束驾驶退还',
                                'make_order_no' => $order['order_no'],
                                'amount' => $returnAmount,
                                'venue'  => $user->special_area_name,
                                'special_area' => $user->special_area,
                            ]);
                        }
                    }
                }

                if($order['billing_method'] == 1){
                    $count =  1; //按次固定一次
                    $shouldTime = $startTime + ($rulesTime * $count); //当前阶段应该结束时间
                    $shouldTime2 = $shouldTime - $time; //阶段剩余多少时间=未使用时间
                    $shouldTime3 = $rulesTime - $shouldTime2; //阶段时间-剩余时间=已使用时间
                    $num = $shouldTime3 / $rulesTime;
//                    $p3 = $rulesAmount * 0.3;  // 中间30%
//                    $p3_last = $rulesAmount * 0.3; // 最后30%
//                    $p1 = $rulesAmount * 0.2;
                    if($num < 0.9){ //超90直接不退钱

                        $returnAmount = intval($rulesAmount * ($shouldTime2 / $rulesTime)); //返回金额 = 阶段金额*当前剩余时间/阶段时间
                        if(($time - $startTime) <= 15){
                            $returnAmount = intval($rulesAmount) - 2; // 上车就扣2电池
                        }
                        if((intval($rulesAmount) - $returnAmount) <= 2){
                            $returnAmount = intval($rulesAmount) - 2; // 上车后驾驶扣费不足2电池的也扣2电池
                        }
                        // 只用了前40%区间，扣4成，剩余6成可退
//                        $returnAmount = intval($p3 + $p3_last);
//                        if($num <= 0.2){ // 如果时长不到20%
//                            $returnAmount = intval($p1 + $p3 + $p3_last); //总共扣20%的钱
//                        }
//                        if ($num >= 0.4 && $num < 0.7) {
//                            // 用完前40%+中间30%，共扣7成，最后3成可退
//                            $returnAmount = intval($p3_last);
//                        }

                        if($order['payment_type'] == 1){
                            WalletService::safeAdjust([
                                'uid' => $user->id,
                                'type' => CuserWalletLog::TypeReturn,
                                'type_name'=>'提前结束驾驶退还',
                                'make_order_no' => $order['order_no'],
                                'amount' => $returnAmount,
                                'venue'  => $user->special_area_name,
                                'special_area' => $user->special_area,
                            ]);
                        }
                        if($order['payment_type'] == 2){
                            WalletService::safeAdjustEnergy([
                                'uid' => $user->id,
                                'type' => CuserWalletLog::TypeReturn,
                                'type_name'=>'提前结束驾驶退还',
                                'make_order_no' => $order['order_no'],
                                'amount' => $returnAmount,
                                'venue'  => $user->special_area_name,
                                'special_area' => $user->special_area,
                            ]);
                        }
                    }
                }
                $receiverJson = json_decode(Redis::get($data['receiver_id'].'_receiver'),true);
                $receiverJson['transmitter_id'] = '0';
                $receiverJson['transmitter_host_port'] = '';
                Redis::set($data['receiver_id'].'_receiver',json_encode($receiverJson));
                if ($returnAmount > 0) {
                    $order['payment_amount'] = $order['payment_amount'] - $returnAmount;
                }
                //结束驾驶 代理商收入 只有电池才收钱
                if($data['payment_type'] == 1) {

                    $agentWallet = AgentWallet::getBalance($order['agent_id']);
                    $balance = $agentWallet['balance'];

                    $updateQuery = AgentWallet::where(['agent_id' => $order['agent_id']]);
                    $affected = $updateQuery->update(['balance' => DB::raw("balance+{$order['payment_amount']}")]);
                    if ($affected != 1) {
                        Log::info("结束驾驶收入金额： {$data['amount']}, 增加失败： {$agentWallet['balance']}");
                    }
                    $afterBalance = $balance + $order['payment_amount'];

                    AgentWalletLog::create([
                        'agent_id' => $order['agent_id'],
                        'type' => 1,
                        'type_name' => '收入',
                        'amount' => $order['payment_amount'],
                        'make_order_no' => $order['order_no'],
                        'phone' => $user['phone_number'],
                        'user_name' => $user['username'],
                        'venue' => $order['venue_name'],
                        'balance' => $afterBalance,
                        'time' => time(),
                    ]);
                }

                $order->update([
                    'reservation_status' => 4,
                    'end_time' => time(),
                    'transmitter_id' => '0',//释放发射机id
                    'payment_amount' => $order['payment_amount'],
                ]);
                $vehicle->update(['vehicle_state' => 1]);
                Redis::del('vehicle'.$vehicle['id']); //结束驾驶解锁车

                $log = [
                    'order_no' => $order['order_no'] ?? '',
                    'amount' => $order['payment_amount'] ?? '',
                    'platform' =>  $request['platform'],
                    'versionCode' =>  $request['versionCode'],
                ];
                Log::info("结束驾驶扣除金额 : " . json_encode($log, 320));
                return  ReponseData::reponseFormat(200,'结束驾驶成功');
            }
        }
        //代理商端处理逻辑
        if($data['agent_id']){

            $data['transmitter_id'] = $request['transmitter_id'] ?? null;
            $data['receiver_id'] = $request['receiver_id'] ?? null;
            $data['vehicle_id'] = $request['vehicle_id'] ?? null;

            if(!$data['transmitter_id']){
                return ReponseData::reponseFormat(2000,'发射机id必传');
            }
            if(!$data['receiver_id']){
                return ReponseData::reponseFormat(2000,'接收机id必传');
            }
            if(!$data['vehicle_id']){
                return ReponseData::reponseFormat(2000,'车辆id必传');
            }
            $vehicle = Vehicle::where('id',$data['vehicle_id'])->first();
            if(!$vehicle){
                return ReponseData::reponseFormat(2000,'未找到该车辆');
            }
            if($data['type'] == 1){
                if($vehicle['vehicle_state'] == 2){
                    return ReponseData::reponseFormat(2000,'车辆正在驾驶中');
                }
                $check = Redis::get('vehicle'.$data['vehicle_id']);

                if($vehicle['vehicle_state'] == 2){
                    return  ReponseData::reponseFormat(2000,'车辆不在空闲中');
                }
                Redis::set($data['transmitter_id'],$data['receiver_id']); //绑定车辆接收机、发射机id
                $vehicle->update(['vehicle_state' => 2,'is_agent_start'=>1]);
                $key = 'agent_start_driving_'.$data['vehicle_id'];
                Redis::setex($key,35,'start');
                $message = '开始驾驶成功';
            }else if($data['type'] == 2){
                $key = 'agent_start_driving_'.$data['vehicle_id'];
                Redis::setex($key,35,'start');
                $message = '继续驾驶成功';
            }elseif($data['type'] == 3){
                Redis::del($data['transmitter_id']); //绑定车辆接收机、发射机id
                $receiverJson = Redis::get($data['receiver_id'].'_receiver');
                $receiverJson = json_decode($receiverJson,true);
                $receiverJson['transmitter_id'] = '0';
                $receiverJson['transmitter_host_port'] = '';
                Redis::set($data['receiver_id'].'_receiver',json_encode($receiverJson));
                $message = '结束驾驶成功';
                $vehicle->update(['vehicle_state' => 1,'is_agent_start'=>0]);
                $key = 'agent_start_driving_'.$data['vehicle_id'];
                Redis::del($key);
                Redis::del('vehicle'.$vehicle['id']); //结束驾驶解锁车

            }else{
                $key = 'agent_start_driving_'.$data['vehicle_id'];
                $message = '驾驶数据错误';
                Redis::setex($key,35,'start');
                return ReponseData::reponseFormat(2000,$message);

            }

            return ReponseData::reponseFormat(200,$message);
        }

    }

    public function reservation($request)
    {
//        $request = $this->decrypt($request['data']);
        $request['platform'] = $request->header('platform') ?? '';
        $request['versionCode'] = $request->header('versionCode') ?? '';

        $data = [
            'uid' => $request['uid'] ?? null,
            'vehicle_id' => $request['vehicle_id'] ?? null,
            'vehicle_name' => $request['vehicle_name'] ?? null,
            'venue_id' => $request['venue_id'] ?? null,
            'venue_name' => $request['venue_name'] ?? null,
            'payment_type' => $request['payment_type'] ?? null,
            'billing_method' => $request['billing_method'] ?? null,
            'billing_rules' => $request['billing_rules'] ?? null,
            'app_transmitter_id' => $request['app_transmitter_id'] ?? null,
        ];


        if($data['app_transmitter_id'] === null){
            return ReponseData::reponseFormat(2000,'app发射机id必传');
        }
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }
        $user = Cuser::where('id',$data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到该用户');
        }

        if($data['payment_type'] === null){
            return ReponseData::reponseFormat(2000,'支付类型必传');
        }
        if($data['billing_method'] === null){
            return ReponseData::reponseFormat(2000,'计费方式必传');
        }

        $lock_key = 'huazhi:reservation:' . $data['uid'].':create:'.$data['vehicle_id'];
        $ret = Redis::set($lock_key, '1','ex','2','nx');
        if(!$ret){
            return ReponseData::reponseFormat(2000,'请勿重复点击');
        }

        $reservationExists = DrivingRecord::where('uid',$data['uid'])->whereIn('reservation_status',[1,2])->first();
        if($reservationExists){
            return ReponseData::reponseFormat(2000,'已有预约单子');
        }

        $orderNo = OrderNo('ZKSJ');
        $agent_id = Vehicle::where('id',$data['vehicle_id'])->value('agent_id');
        DrivingRecord::create([
            'uid' => $data['uid'],
            'user_name' => $user['username'],
            'order_no' => $orderNo,
            'vehicle_id' => $data['vehicle_id'],
            'vehicle_name' => $data['vehicle_name'],
            'venue_id' => $data['venue_id'],
            'venue_name' => $data['venue_name'],
            'billing_rules' => json_encode($data['billing_rules']),
            'special_area' => $user['special_area'],
            'special_area_name' => $user['special_area_name'],
            'phone' => $user['phone_number'],
            'reservation_status' => 1,
            'payment_type' => $data['payment_type'],
            'billing_method' => $data['billing_method'],
            'order_time' => time(),
            'agent_id' => $agent_id,
            'transmitter_id' => $data['app_transmitter_id'],
            'head_shot' => $user['head_shot'],
        ]);
        $list = [
            'vehicle_name'=>$data['vehicle_name'],
            'time' => Date('Y-m-d H:i:s'),
            'payment_type' => $data['payment_type'],
            'billing_method' => $data['billing_method'],
            'order_no' => $orderNo,
            'transmitter_id' => $data['app_transmitter_id'],
            'people_number' => DrivingRecord::where('vehicle_id', $data['vehicle_id'])->where('reservation_status', 3)->count(),//排队人数

        ];
        $log = [
            'order_no' => $orderNo,
            'platform' =>  $request['platform'],
            'versionCode' =>  $request['versionCode'],
        ];
        Log::info('预约单子：'.json_encode($log));
        return ReponseData::reponseFormatList(200,'预约成功',$list);
    }

    public function cancelReservation($request)
    {
        $data = [
            'uid' => $request['uid'] ?? null,
            'order_no' => $request['order_no'] ?? null,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }
        $user = Cuser::where('id',$data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到该用户');
        }
        $order = DrivingRecord::where('order_no',$data['order_no'])->first();
        if(!$order){
            return ReponseData::reponseFormat(2000,'未找到该订单');
        }
        $order->reservation_status = 5;
        $order->save();
        return ReponseData::reponseFormat(200,'取消预约成功');
    }

    public function depositList($request)
    {
//        $request = $this->decrypt($request['data']);

        $uid = $request['uid'] ?? null;

        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必须传');
        }
        $data = [
            [
                'amount' => 10,
            ],
            [
                'amount' => 20,
            ],
            [
                'amount' => 50,
            ],
            [
                'amount' => 100,
            ],
            [
                'amount' => 200,
            ],
            [
                'amount' => 500,
            ],
        ];

        return  ReponseData::reponseFormatList(200,'成功',$data);
    }

    public function depositActivityList($request)
    {
//        $request = $this->decrypt($request['data']);

        $uid = $request['uid'] ?? null;

        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必须传');
        }
        $list = DepositActivity::select('activity_id','payment_amount','send_energy','type')->where('type',1)->get();

        return ReponseData::reponseFormatList(200,'成功',$list);
    }

    public function chackUnusualReservation($request)
    {
//        $request = $this->decrypt($request['data']);

        $uid = $request['uid'] ?? null;
        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必须传');
        }

        $user = Cuser::where('id',$uid)->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到该用户');
        }
        $reservationExists = DrivingRecord::where('uid',$uid)->whereIn('reservation_status',[1,2])->first();
        $message = '成功';
        $returnRespData = [
            'order_no' => '',
            'status'=>3,
        ];
        if($reservationExists){
            $returnRespData = [
                'order_no' => $reservationExists['order_no'],
                'status' => 1,//已有预约单
            ];
            $message = '有已预约的单子未取消哦';
        }
        $drivingExists = DrivingRecord::where('uid',$uid)->where('reservation_status',3)->first();

        if($drivingExists){
            $returnRespData = [
                'order_no' => $drivingExists['order_no'],
                'status' => 2,//已异常的单子
            ];
        }
        return ReponseData::reponseFormatList(200,$message,$returnRespData);
    }

    public function chackStartDriving($request)
    {
//        $request = $this->decrypt($request['data']);

        $uid = $request['uid'] ?? null;
        $vehicle_id = $request['vehicle_id'] ?? null;
//        if(!$uid){
//            return ReponseData::reponseFormat(2000,'用户id必须传');
//        }
        if(!$vehicle_id){
            return ReponseData::reponseFormat(2000,'驾驶的车辆id必传');
        }
//        $user = Cuser::where('id',$uid)->first();
//        if(!$user){
//            return ReponseData::reponseFormat(2000,'未找到该用户');
//        }

        $vehicle = Vehicle::where('id',$vehicle_id)->first();
        $respData = [
            'vehicle_id' => $vehicle_id,
            'state' => 0,
        ];
        if(Redis::get('vehicle'.$vehicle_id)){
            return ReponseData::reponseFormatList(200,'车辆不再空闲中，请等待',$respData);
        }

        $key = 'agent_start_driving_'.$vehicle_id;
        $a = Redis::get($key);
        if($a){
            return ReponseData::reponseFormatList(2000,'车辆不再空闲中，请等待',$respData);
        }
        if($vehicle['is_agent_start'] == 1){
            return ReponseData::reponseFormatList(2000,'车辆不再空闲中，请等待',$respData);
        }
        if($vehicle['vehicle_state'] == 1){
//            Redis::setex($vehicle_id,20,'freeze');
            $respData = [
                'vehicle_id' => $vehicle_id,
                'state' => 1,
        ];
            return ReponseData::reponseFormatList(200,'成功',$respData);
        }
        if($respData['state'] === 0){
            return  ReponseData::reponseFormat(2000,'车辆不再空闲中，请等待');
        }
        return ReponseData::reponseFormatList(2000,'车辆不再空闲中，请等待',$respData);
    }

    public function Banner($request)
    {
//        $request = $this->decrypt($request['data']);

        $data = [
            'uid' => $request['uid'] ?? null,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必须传');
        }

        $user = Cuser::where('id',$data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到该用户');
        }

        $banner = Banner::where('status',1)->get();

        return ReponseData::reponseFormatList(200,'成功',$banner);
    }

    public function AppVersion($request)
    {
        $type = $request['type']; // 1苹果 2安卓

        $list = AppVersion::select('version_mark','type','update_content','forced_updating')->where('type',$type)->first();


        return ReponseData::reponseFormatList(200,'成功',$list);
    }

    public function lockDriving($request)
    {
        $vehicle_id = $request['vehicle_id'] ?? null;
        if(!$vehicle_id){
            return ReponseData::reponseFormat(2000,'车辆id必传');
        }

        $vehicle = Vehicle::where('id',$vehicle_id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'车辆不存在');

        }

        Redis::setex('vehicle'.$vehicle_id,20,'freeze');

        return ReponseData::reponseFormat(200,'点击驾驶锁车成功');

    }
}
