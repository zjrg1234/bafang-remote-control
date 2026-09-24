<?php
namespace App\Http\Service;


use App\Http\Repo\LoginRepo;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserEnergyLog;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Models\DepositActivity;
use App\Models\DepositLog;
use App\Models\ReponseData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class KsService
{
    protected $repo;
    public function __construct()
    {
        $this->repo = new LoginRepo();
    }
    public function ksLogin($request)
    {

//        $phoneCode = $request['phone_code'];
        $code = $request->post('ks_code');
        $encryptedData = $request->post('encrypted_data');
        $iv = $request->post('iv');



        if(empty($code)){
            return ReponseData::reponseFormat(2000,'code必传');
        }
        $ksId     = config('ks.ks_appid');
        $ksSecret = config('ks.ks_secret');
        $loginService = new LoginService();
        $url = "https://open.kuaishou.com/oauth2/mp/code2session";

        // 快手 code2session 接口
        $resp = Http::asForm()->post($url, [
            'app_id'     => $ksId,
            'app_secret' => $ksSecret,
            'js_code'    => $code,
        ]);

        $result = $resp->json();
        if($result['result'] !== 1){
            return ReponseData::reponseFormat(2000,'登陆校验失败',$result);
        }

        $ksOpenid = $result['open_id'];
        $sessionKey = $result['session_key'];
        // 如果前端传了加密参数，直接在登录接口解密手机号
        if (!empty($encryptedData) && !empty($iv)) {
            $phoneEncode = $this->aes128CbcDecrypt($sessionKey, $encryptedData, $iv);
            if ($phoneEncode) {
                $phoneArr = json_decode($phoneEncode, true);
                $phone = $phoneArr['phoneNumber'];
            }else{
                return ReponseData::reponseFormat(2000,'登陆失败,获取手机号错误');
            }
        }else{
            return ReponseData::reponseFormat(2000,'手机号必传');
        }
        $userInfo = $this->repo->getUserByMobile($phone);
        if(!isset($userInfo)) {
            $special_area = CuserAgent::where('superior_agent_id',0)->inRandomOrder()->first();
            $insertData = [
                'phone_number' => $phone,
                'special_area' => $special_area['id'],
                'special_area_name' => $special_area['agent_name'],
                'register_time' => time(),
                'head_shot' => 'https://bfyk.oss-cn-hangzhou.aliyuncs.com/yk/image/ZKSJ_1785999958KSGK.jpeg', //默认头像
                'username' => '八方远控' . mt_rand(10000000, 99999999),
                'show_id' => mt_rand(10000000, 99999999),
                'ks_openid' => $ksOpenid,
                'session_key' => $sessionKey,

            ];

            $user = $this->repo->createUsers($insertData);
            $balance = CuserWallet::getBalance($user['id'], $special_area['id']);
            if ($user && isset($balance)) {
                $response = $loginService->registerLogin($user);
                return ReponseData::reponseData($response);
            }
        }
        if($userInfo['is_cancel'] == 1){
            $userInfo->update(['is_cancel'=>0]);
        }
//                    if($userInfo['is_locked'] == 1){
//                        $userInfo->update(['is_locked'=>0]);
//                    }
        if($userInfo['is_delete'] == 1){
            return ReponseData::reponseFormat(2000,'账号被删除,请联系管理员!');
        }
        $nowTime                 = time();
//            $sessionKey              = base64_encode(md5($userInfo['id'].$userInfo['user_name'].$nowTime));
        $key = 'token_'.$userInfo['id'];
        Redis::set($key, $sessionKey);

        $updateData = [
            'last_online_time' => $nowTime,
            'session_key' => $sessionKey,
            'ks_openid' => $ksOpenid,
        ];
        Cuser::where('id', $userInfo['id'])->update($updateData);
        $response =  [
            'id' => $userInfo['id'],
            'special_area' => $userInfo['special_area'],
            'session_key' => $sessionKey,
            'new_user' => 0,
        ];
        $responseData = $response;
        return ReponseData::reponseFormatList(200,'成功',$responseData);
    }

    //快手支付、支付宝/微信共用
    public function ksDeposit($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
            'amount' => $request['amount'] ?? null,
            'activity_id' => $request['activity_id'] ?? null,
            'pay_channel' => $request['pay_channel'] ?? null, //WECHAT/ZFB
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }
        $user = Cuser::where('id', $data['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2004,'未查询到该用户哦!');
        }
        $orderPrefix = 'KS_'.$data['pay_channel'];
        $depositOrder = [
            'uid' => $request['uid'],
            'amount' => $request['amount'],
            'user_name' => $user['username'],
            'special_area'=> $user['special_area'],
            'special_area_name'=> $user['special_area_name'],
            'phone_number' => $user['phone_number'],
            'time' => time(),
            'type' => 0,
            'pay_type' => 5,//1微信，支付宝，3银行卡，4momo
            'order_no' => orderNo($orderPrefix),
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
            $depositOrder['subject'] = '电池购买';
            $resp = $this->createOrder($depositOrder,$data['pay_channel'],$user['ks_openid']);
            return ReponseData::reponseFormatList(200,'下单成功',$resp);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return $e->getMessage();
        }


    }

    public function createOrder($depositOrder,$payChannel,$ksOpenid)
    {
        $url = "https://open.kuaishou.com/openapi/mp/developer/epay/create_order_with_channel";
        $ksId     = config('ks.ks_appid');
        $ksSecret = config('ks.ks_secret');
        $ksNotifyUrl = config('ks.ks_notify_url');
        $accessToken = $this->getAccessToken();
        $params = [
            'app_id'        => $ksId,
            'app_secret'    => $ksSecret,
            'out_order_no'  => $depositOrder['order_no'],
            'total_amount'  => intval($depositOrder['amount'] * 100), //转分
            'subject'       => '电池购买',
            'body'          => '电池购买',
            'notify_url'    => $ksNotifyUrl,
            'pay_channel'   => $payChannel,
            'open_id'       =>$ksOpenid,
            'expire_time'   => time() + 1800, // 新增！30分钟过期，unix时间戳(秒)
            'detail'        => '电池商品详情描述',

        ];
        ksort($params);
        $str = '';
        foreach ($params as $k=>$v) {
            if($v !== '' && $v !== null){
                $str .= $k . '=' . $v . '&';
            }
        }
        $str .= 'app_secret='.$ksSecret ;
        $sign = md5($str);

        $postData = $params;
        $postData['sign'] = $sign; // 追加签名

// url带上app_id
        $finalUrl = $url . '?app_id=' . $ksId ;

        $payResp = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($finalUrl, $postData);

        $payData = $payResp->json();
        if ($payData['result'] !== 1) {
            return response()->json([
                'code' => -1,
                'msg'  => '创建支付订单失败',
                'err'  => $payData
            ]);
        }
        return [
            'ks_order_id'  => $payData['order_id'],
            'out_order_no' => $depositOrder['order_no'],
            'pay_channel'  => $payChannel
        ];
    }

    public function ksNotify($request)
    {
        $params = $request->all();
        $sign = $params['sign'];
        $appSecret = config('ks.ks_secret');
        $check = $this->ksSignVerify($params, $appSecret, $sign);
        if (!$check) {
            return response('fail', 400);
        }
        if ($params['status'] == 'SUCCESS') {
            try {
                // 如果不是支付成功状态，直接抛弃

                $outTradeNo = $params['out_order_no'];
                $realPayCent = $params['order_amount'] ?? 0; // 单位：分
                $payAmount = $realPayCent / 100; // 转为元
                $tradeNo = $params['trade_no'];
                $order = DepositLog::where('order_no', $outTradeNo)->first();
                if (!$order) {
                    return response('fail', 400);
                }

                if ($order->type == 1 || $order->type == 2) {
                    Log::info('支付回调订单：' . $outTradeNo . '已完成，重复回调');
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
                    'type_name' => '充值',
                    'make_order_no' => $order['order_no'],
                    'amount' => $payAmount,
                    'venue' => $order->special_area_name,
                    'special_area' => $order->special_area,
                ]);
                if ($order->activity_id != '') {
                    $sendMoney = $order->sendMoney;
                    WalletService::safeAdjustEnergy(
                        [
                            'uid' => $order->uid,
                            'type' => CuserEnergyLog::TypeDeposit,
                            'type_name' => '充值赠送',
                            'make_order_no' => $order['order_no'],
                            'amount' => $sendMoney,
                            'venue' => $order->special_area_name,
                            'special_area' => $order->special_area,
                            'activity_id' => $order->activity_id,

                        ]
                    );
                }
                return response()->json(['code' => 'SUCCESS', 'message' => '成功']);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return response('fail', 400);

            }
        }else{
            return response('fail', 400);
        }
    }

    // AES-128-CBC 解密手机号
    private function aes128CbcDecrypt(string $sessionKey, string $encryptedData, string $iv): string|false
    {
        $key = base64_decode($sessionKey);
        $iv  = base64_decode($iv);
        $data = base64_decode($encryptedData);
        return openssl_decrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    // 快手支付回调签名校验
    private function ksSignVerify(array $params, string $secret, string $sign): bool
    {
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if ($k !== 'sign') {
                $str .= $k . $v;
            }
        }
        $str .= $secret;
        $calc = md5($str);
        return strcasecmp($calc, $sign) === 0;
    }

    public function getAccessToken()
    {
        $ksId     = config('ks.ks_appid');
        $ksSecret = config('ks.ks_secret');
        $cacheKey = 'ks_access_token_' . $ksId;
        $token = Redis::get($cacheKey);
        if ($token) return $token;

        $url = "https://open.kuaishou.com/oauth2/access_token";
        $resp = Http::asForm()->post($url, [
            'app_id'     => $ksId,
            'app_secret' => $ksSecret,
            'grant_type' => 'client_credentials'
        ]);
        $res = $resp->json();
        if(empty($res) || $res['result'] !== 1){
            throw new \Exception('获取快手access_token失败：'.json_encode($res));
        }
        $token = $res['access_token'];
        $expire = $res['expires_in'] - 60; // 提前60秒过期
        Redis::setex($cacheKey, $expire, $token);
        return $token;
    }
}
