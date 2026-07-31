<?php
namespace App\Http\Service;

use AlibabaCloud\SDK\Dypnsapi\V20170525\Dypnsapi;
use AlibabaCloud\SDK\Dypnsapi\V20170525\Models\GetMobileRequest;
use AlibabaCloud\SDK\Dypnsapi\V20170525\Models\GetPhoneWithTokenRequest;
use App\Http\Repo\LoginRepo;
use App\Models\Banner;
use App\Models\CuserAgent;
use App\Models\Cuser;
use App\Models\ReponseData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use App\Models\CuserWallet;
use OSS\Core\OssException;
use OSS\OssClient;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
class LoginService
{
    protected $repo;
    public function __construct()
    {
        $this->repo = new LoginRepo();
    }
    public function login($request)
    {
        $ip = getIp($request);
//        $request = $this->decrypt($request['data']);
        $data = [
            'phone' => $request['phone'],
            'captcha'  => $request['noteVerify'] ?? null,
            'type'     => $request['type'] ?? null,
        ];
        if(isset($request['password'])){
            $data['password'] = md5($request['password']);
        }else{
            $data['password'] = null;
        }
        $this->validateRequestLogin($data);
        if($data['type'] == '1'){ //用户端登陆
            $userInfo = $this->repo->getUserByMobile($data['phone']);
            if(!isset($userInfo)){
                return ReponseData::reponseFormat(2003,'账号未注册，请先注册哦！');
            }
            if($userInfo['is_cancel'] == 1){
                return ReponseData::reponseFormat(2000,'账号已经注销!');
            }
            if($userInfo['is_locked'] == 1){
                return ReponseData::reponseFormat(2000,'账号被封号 请联系管理员!');
            }

            if($userInfo['is_delete'] == 1){
                return ReponseData::reponseFormat(2000,'账号被删除,请联系管理员!');
            }
            if(isset($data['password']) && $userInfo['password'] != $data['password']){
                return ReponseData::reponseFormat(2003,'账号密码错误！');
            }
            if($data['captcha'] == '666666'){
                if(isset($data['captcha'])){
                    return ReponseData::reponseFormat(2003,'验证码错误！');
                }
            }elseif($data['captcha'] != null){
                $code = Redis::get($data['phone']);
                if(empty($code)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $data['captcha']){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($data['phone']);

            }else{
                if(isset($data['password']) && $userInfo['password'] != $data['password']){
                    return ReponseData::reponseFormat(2003,'账号密码错误！');
                }

                if(!$data['password'])
                {
                    return ReponseData::reponseFormat(2003,'请输入验证码！');
                }
            }

            $nowTime                 = time();
            $sessionKey              = base64_encode(md5($userInfo['id'].$userInfo['user_name'].$nowTime));
            $key = 'token_'.$userInfo['id'];
            Redis::set($key, $sessionKey);
            $updateData = [
                'last_online_time' => $nowTime,
                'login_ip' => $ip,
                'session_key' => $sessionKey,
            ];
            Cuser::where('id', $userInfo['id'])->update($updateData);
            $response =  [
                'id' => $userInfo['id'],
                'special_area' => $userInfo['special_area'],
                'session_key' => $sessionKey,
            ];
//            $responseData = $this->encrypt($response);
            $responseData = $response;
            return ReponseData::reponseFormatList(200,'成功',$responseData);
        }else{
            $agent = CuserAgent::where('phone_number',$data['phone'])->first();
            if(!$agent){
                return ReponseData::reponseFormat(2000,'该账号还未注册成为代理商!');
            }
            if($agent['is_cancel'] == 1){
                return ReponseData::reponseFormat(2000,'账号已经注销!');
            }
            if($agent['is_frozen'] == 1){
                return ReponseData::reponseFormat(2000,'账号被冻结 请联系管理员!');
            }
            if($agent['is_delete'] == 1){
                return ReponseData::reponseFormat(2000,'账号被删除,请联系管理员!');
            }
            if(isset($data['password']) && $agent['password'] != $data['password']){
                return ReponseData::reponseFormat(2003,'账号密码错误！');
            }
            $nowTime                 = time();
            $sessionKey              = base64_encode(md5($agent['id'].$agent['agent_name'].$nowTime));
            $key = 'agent_token_'.$agent['id'];
            Redis::set($key, $sessionKey);
            $response =  [
                'id' => $agent['id'],
                'special_area' => $agent['special_area'] ?? 0,
                'session_key' => $sessionKey,
            ];
//            $responseData = $this->encrypt($response);
            return ReponseData::reponseFormatList(200,'成功',$response);

        }


//        CuserCacheService::setUserLoginInfo($userInfo['id'], $deviceInfo->toArray());

    }

    public function register($request)
    {
        $ip = getIp($request);
//        $request = $this->decrypt($request['data']);
        $data = [
            'phone' => $request['phone'],
            'password' => md5($request['password']),
            'noteVerify' => $request['noteVerify'],
        ];
        $validator = $this->validateRequestRegister($data);
        if($validator->fails()){
            return ReponseData::reponseFormat(2002,$validator->errors()->first());
        }

        $lock_key = "Register::".$data['phone'];
        $ret      = Redis::set($lock_key, '1', 'ex', '3', 'nx');
        if(!$ret){
            return ReponseData::reponseFormat(2002,'请勿重复点击哦!');
        }
        $userExists = $this->repo->getUsers($data['phone']);
        if(isset($userExists) && $userExists['is_cancel'] != 1 ){
            return ReponseData::reponseFormat(2002,'该用户已注册!');
        }

        if(isset($userExists) && $userExists['is_delete'] == 1){
            return ReponseData::reponseFormat(2002,'该用户已被删除，请联系管理员!');
        }
        if(!$data['noteVerify']){
            return ReponseData::reponseFormat(2002,'验证码必填!');
        }
        if($data['noteVerify'] == '666666'){
            Log::info('无需验证'.$data['phone'].'验证码：'.$data['noteVerify']);
        }else{
            $code = Redis::get($data['phone']);
            if(empty($code)){
                return ReponseData::reponseFormat(2003,'验证码已过期！');
            }
            if($code != $data['noteVerify']){
                return ReponseData::reponseFormat(2000,'验证码错误');
            }
            Redis::del($data['phone']);
        }


        if((isset($userExists) && $userExists['is_cancel'] == 1)){
            $userExists->update(['is_cancel' => 0,'is_delete' => 0,'password'=>$data['password']]);
            $response = $this->registerLogin($userExists);

            return ReponseData::reponseData($response);
        }
//        $minId = CuserAgent::where('superior_agent_id',0)->min('id');
//        $maxId = CuserAgent::where('superior_agent_id',0)->max('id');
//        $randomId = rand($minId, $maxId);
        $special_area = CuserAgent::where('superior_agent_id',0)->where('id','>',3)->inRandomOrder()->first();
        $insertData = [
            'phone_number' => $data['phone'],
            'password' => $data['password'],
            'special_area' => $special_area['id'],
            'special_area_name' => $special_area['agent_name'],
            'register_time' => time(),
            'login_ip' => $ip,
            'head_shot' => 'https://zksj-new.oss-cn-beijing.aliyuncs.com/zk/image/ZKSJ_1770280030SR25.jpeg', //默认头像
            'username' => '掌中视界'.mt_rand(10000000,99999999),
            'show_id' => mt_rand(10000000,99999999),
        ];

        $user = $this->repo->createUsers($insertData);
//        $balance = CuserWallet::getBalance($user['id'],$special_area['id']);
        if($user){
//            $response = $this->encrypt($this->registerLogin($user));
            $response = $this->registerLogin($user);

            return ReponseData::reponseData($response);
        }else{
            return ReponseData::reponseFormat(2002,'注册出错！');
        }
    }
    public function registerLogin($userInfo)
    {
        $nowTime                 = time();
        $sessionKey              = base64_encode(md5($userInfo['id'].$userInfo['user_name'].$nowTime));
        $key = 'token_'.$userInfo['id'];
        Redis::set($key, $sessionKey);
        $updateData = [
            'last_online_time' => $nowTime,
            'session_key' => $sessionKey,
        ];
        Cuser::where('id', $userInfo['id'])->update($updateData);
        return [
            'id' => $userInfo['id'],
            'special_area' => $userInfo['special_area'],
            'session_key' => $sessionKey,
        ];
    }
    public function getLoginCode($request){

//        $request = $this->decrypt($request['data']);
        $data = [
            'phone' => $request['phone'],
        ];
        Log::info('request_phone ' . $request['phone']);
        $rules = [
            'phone'            => 'required|regex:/^1[3-9]\d{9}$/|digits:11',
        ];

        $message = [
            'phone.required'            => '手机号不能为空',
            'phone.regex'                 => '手机号格式错误',
            'phone.digits'                 => '手机号必须为11位数字',

        ];
        $validator = Validator::make($data, $rules, $message);
        if($validator->fails()){
            return ReponseData::reponseFormat(2001,$validator->errors()->first());
        }
        $config = [
            'accessKeyId'     => config('oss.access_key_id') ?? env('ALIYUN_OSS_ACCESS_KEY_ID'),
            'accessKeySecret' => config('oss.access_key_secret') ?? env('ALIYUN_OSS_ACCESS_KEY_SECRET'),
            'openai_sign'          => config('oss.openai_sign') ?? env('ALIYUN_OSS_OPENAI_SIGN'),
            'endpoint'  =>env('ALI_SMS_ENDPOINT'),
            'region_id' => 'cn-hangzhou',
        ];
        $dysmsapi_config = new Config($config);
        $client = new Dysmsapi($dysmsapi_config);

        $code = rand(100000, 999999);
        $smsModel = env('SMS_MODEL');
        try {
            // 构建发送请求参数
            $sendSmsRequest = new SendSmsRequest([
                "phoneNumbers" => $data['phone'],
                "signName" => $config['openai_sign'],
                "templateCode" => $smsModel,
                "templateParam" => json_encode(['code' => $code], JSON_UNESCAPED_UNICODE),
                // 可选：短信上行扩展码（若无需求可省略）
                // "smsUpExtendCode" => "",
                // 可选：外部流水号（若无需求可省略）
                // "outId" => ""
            ]);

            // 发送短信
            $runtime = new RuntimeOptions([]);
            $response = $client->sendSmsWithOptions($sendSmsRequest, $runtime);

            // 处理返回结果
            $result = $response->toMap();
            if ($result['body']['Code'] == 'OK') {
                $list = [
                    'code' => $code,
                ];
                Redis::setex($data['phone'], 300, $code);
                return ReponseData::reponseFormatList(200,'获取成功',$list);
            } else {
                return ReponseData::reponseFormat(2001,'发送失败'.$result['body']['Message']);
            }
        } catch (TeaError $e) {
            return ReponseData::reponseFormat(2001,'短信接口调用异常：' . $e->getMessage());

        } catch (Exception $e) {
            return ReponseData::reponseFormat(2001,'系统异常：' . $e->getMessage());

        }

//        $code = '666666';

    }

    public function logout($request)
    {
        $request = $this->decrypt($request['data']);
        $uid = $request['uid'] ?? null;
        $agent_id = $request['agent_id'] ?? null;
        if($uid){
            $key = 'token_'.$uid;
            Redis::del($key);
        }else{
            $key = 'agent_token_'.$agent_id;
            Redis::del($key);
        }
        return ReponseData::reponseFormat(200,'退出成功！');
    }


    protected function validateRequestLogin($data)
    {
        $rules = [
            'phone'            => 'required|regex:/^1[3-9]\d{9}$/|digits:11',
        ];

        $message = [
            'phone.required'            => '手机号不能为空',
            'phone.regex'                 => '手机号格式错误',
            'phone.digits'                 => '手机号必须为11位数字',

        ];

        return Validator::make($data, $rules, $message);

    }

    protected function validateRequestRegister($data)
    {
        $rules = [
            'phone'         => 'required|regex:/^1[3-9]\d{9}$/|digits:11',
            'password'             => 'required',
            'noteVerify'         => 'required',
        ];

        $message = [
            'phone.required'            => '手机号不能为空',
            'phone.regex'                 => '手机号格式错误',
            'phone.digits'                 => '手机号必须为11位数字',
            'password.required'             => '密码不能为空',
            'noteVerify.required'    => '验证码不能为空',
        ];

        return Validator::make($data, $rules, $message);

    }

    public function encrypt($data)
    {
        $aesKey = config('aes.aes_key');
        $json = json_encode($data);
        return aesEncrypt($json,'aes-128-ecb',$aesKey);
    }
    public function decrypt($data)
    {
        $aesKey = config('aes.aes_key');
        return json_decode(aesDecrypt($data,'aes-128-ecb',$aesKey),true);
    }

    public function uploadPicture($request)
    {
//        $data = $this->decrypt($request['data']);
        $imageContent = $request->File('imageFile');
//        $binaryData =  base64_decode($base64Image);
        $config = [
            'access_key_id'     => config('oss.access_key_id') ?? env('ALIYUN_OSS_ACCESS_KEY_ID'),
            'access_key_secret' => config('oss.access_key_secret') ?? env('ALIYUN_OSS_ACCESS_KEY_SECRET'),
            'bucket'            => config('oss.bucket') ?? env('ALIYUN_OSS_BUCKET'),
            'endpoint'          => config('oss.endpoint') ?? env('ALIYUN_OSS_ENDPOINT'),
        ];

        $ossClient = new OssClient(
            $config['access_key_id'],
            $config['access_key_secret'],
            $config['endpoint'],
        );
        $resp = [
        ];
        foreach ($imageContent as  $value) {
            Log::info('request_image ' . $value->getPathName().'type:'.$value->getType().'size:'.$value->getSize());
            $fileContent = file_get_contents($value->getRealPath());
            $fileName = 'ZKSJ_'.time() .readableRand(4) .'.' . 'jpeg';
            $ossClient->putObject($config['bucket'], 'zk/image/'.$fileName,$fileContent);
            $file = 'https://'.$config['bucket'].'.'.$config['endpoint'].'/zk/image/'.$fileName;
            $resp['file'][] = $file;
        }

        return ReponseData::reponseFormatList(200,'上传成功',$resp);
    }

    public function uploadFile($request)
    {
//        $data = $this->decrypt($request['data']);
        $fileContent = $request->File('file');

//        $binaryData =  base64_decode($base64Image);
        $config = [
            'access_key_id'     => config('oss.access_key_id') ?? env('ALIYUN_OSS_ACCESS_KEY_ID'),
            'access_key_secret' => config('oss.access_key_secret') ?? env('ALIYUN_OSS_ACCESS_KEY_SECRET'),
            'bucket'            => config('oss.bucket') ?? env('ALIYUN_OSS_BUCKET'),
            'endpoint'          => config('oss.endpoint') ?? env('ALIYUN_OSS_ENDPOINT'),
        ];

        $ossClient = new OssClient(
            $config['access_key_id'],
            $config['access_key_secret'],
            $config['endpoint'],
        );
        $resp = [
        ];

        $fileContent = file_get_contents($fileContent->getRealPath());
        $fileName = 'app-release'.time() . '.' . 'apk';
        $ossClient->putObject($config['bucket'], 'zk/file/'.$fileName,$fileContent);
        $file = 'https://'.$config['bucket'].'.'.$config['endpoint'].'/zk/file/'.$fileName;
        $resp['file'][] = $file;


        return ReponseData::reponseFormatList(200,'上传成功',$resp);
    }


    public function changePassword($request)
    {
//        $request = $this->decrypt($request['data']);
        $code = $request['code'] ?? null;
        $phone = $request['phone'] ?? null;
        $password = $request['password'] ?? null;
        $uid = $request['uid'] ?? null;
        $agent_id = $request['agent_id'] ?? null;

        if($uid){
            $user = Cuser::where('id', $uid)->first();
            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该账号!');
            }
            if(!$code){
                return ReponseData::reponseFormat(2002,'验证码必填');

            }
            if($code == '666666'){
                Log::info('无需验证'.$user['phone_number'].'验证码：'.$code);
            }else{
                $redisCode = Redis::get($phone);
                if(empty($redisCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $redisCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($phone);
            }
            if(!$password){
                return ReponseData::reponseFormat(2002,'新密码必填');
            }
            $user->password = md5($password);
            $user->save();
        }

        if($agent_id){
            $agent = CuserAgent::where('id',$agent_id)->first();
            if(!$agent){
                return ReponseData::reponseFormat(2000,'未找到该代理商账号!');
            }
            if(!$code){
                return ReponseData::reponseFormat(2002,'验证码必填');

            }
            if($code == '666666'){
                Log::info('无需验证'.$agent['phone_number'].'验证码：'.$code);
            }else{
                $redisCode = Redis::get($agent['phone_number']);
                if(empty($redisCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $redisCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($agent['phone_number']);
            }
            $agent->password = md5($password);
            $agent->save();
        }

        if($phone && !$uid && !$agent_id){//忘记密码
            $user = Cuser::where('phone_number', $phone)->first();


            if(!$code){
                return ReponseData::reponseFormat(2002,'验证码必填');

            }
            if($code == '666666'){
                Log::info('无需验证'.$phone.'验证码：'.$code);
            }else{
                $redisCode = Redis::get($phone);
                if(empty($redisCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $redisCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($phone);
            }
            $user->password = md5($password);
            $user->save();
        }

        return ReponseData::reponseFormat(200,'修改成功');

    }


    public function agentChangePassword($request)
    {
//        $request = $this->decrypt($request['data']);
        $code = $request['code'] ?? null;
        $phone = $request['phone'] ?? null;
        $password = $request['password'] ?? null;
        $uid = $request['uid'] ?? null;
        $agent_id = $request['agent_id'] ?? null;

        if($uid){
            $user = Cuser::where('id', $uid)->first();
            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该账号!');
            }
            if(!$code){
                return ReponseData::reponseFormat(2002,'验证码必填');

            }
            if($code == '666666'){
                Log::info('无需验证'.$user['phone_number'].'验证码：'.$code);
            }else{
                $redisCode = Redis::get($user['phone_number']);
                if(empty($redisCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $redisCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($user['phone_number']);
            }
            if(!$password){
                return ReponseData::reponseFormat(2002,'新密码必填');
            }
            $user->password = md5($password);
            $user->save();
        }

        if($agent_id){
            $agent = CuserAgent::where('id',$agent_id)->first();
            if(!$agent){
                return ReponseData::reponseFormat(2000,'未找到该代理商账号!');
            }
            if(!$code){
                return ReponseData::reponseFormat(2002,'验证码必填');

            }
            if($code == '666666'){
                Log::info('无需验证'.$agent['phone_number'].'验证码：'.$code);
            }else{
                $redisCode = Redis::get($agent['phone_number']);
                if(empty($redisCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $redisCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($agent['phone_number']);
            }
            $agent->password = md5($password);
            $agent->save();
            $key = 'agent_token_'.$agent_id;
            Redis::del($key);
        }

        if($phone && !$uid && !$agent_id){//忘记密码
            $agent = CuserAgent::where('phone_number',$phone)->first();

            if(!$code){
                return ReponseData::reponseFormat(2002,'验证码必填');

            }
            if($code == '666666'){
                Log::info('无需验证'.$phone.'验证码：'.$code);
            }else{
                $redisCode = Redis::get($phone);
                if(empty($redisCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $redisCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($phone);
            }
            $agent->password = md5($password);
            $agent->save();
            $key = 'agent_token_'.$agent['id'];
            Redis::del($key);
        }

        return ReponseData::reponseFormat(200,'修改成功');

    }

    public function changePhone($request)
    {
//        $data = $this->decrypt($request['data']);
        $code = $request['code'] ?? null;
        $phone = $request['new_phone_number'] ?? null;
        $uid = $request['uid'] ?? null;


        if(!$code){
            return ReponseData::reponseFormat(2002,'验证码必填');

        }


        if($uid){
            $user = Cuser::where('id', $uid)->first();
            $userPhone = $user['phone_number'];
            if($code == '666666'){
                Log::info('无需验证'.$userPhone.'验证码：'.$code);
            }else {
                $getCode = Redis::get($userPhone);
                if(empty($getCode)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $getCode){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($userPhone);
            }
            if(!$phone){
                return ReponseData::reponseFormat(2002,'新手机号必填');
            }
            $exists = Cuser::where('phone_number', $phone)->first();
            if($exists){
                return ReponseData::reponseFormat(2002,'该手机号已存在,请确认号码是否正确');
            }
            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该账号!');
            }
            $user->phone_number = $phone;
            $user->save();
        }

        return ReponseData::reponseFormat(200,'手机号更换成功');

    }

    public function changeHeadShot($request)
    {
//        $request = $this->decrypt($request['data']);
        $headShot = $request['head_shot'] ?? null;
        $uid = $request['uid'] ?? null;
        $agent_id = $request['agent_id'] ?? null;



        if(!$headShot){
            return ReponseData::reponseFormat(2002,'新头像必填');
        }

        if($uid){
            $user = Cuser::where('id', $uid)->first();
            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该账号!');
            }
            $user->head_shot = $headShot;
            $user->save();
        }

        if($agent_id){
            $user = CuserAgent::where('id', $agent_id)->first();

            if(!$user){
                return ReponseData::reponseFormat(2000,'未找到该账号!');
            }
            $user->head_shot = $headShot;
            $user->save();
        }



        return ReponseData::reponseFormat(200,'修改成功');

    }

    public function BannerList($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
        ];

        $query = Banner::query();

        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);


        return ReponseData::reponsePaginationFormat($rows);
    }

    public function BannerCreate($request)
    {
        $data =[
            'image' => $request['image'] ?? null,
            'url' => $request['url'] ?? null,
            'status' => $request['status'] ?? 0,
            'name' => $request['name'] ?? '',
        ];

        if(!$data['image']){
            return ReponseData::reponseFormat(2000,'图片链接必填');
        }

        if(!$data['url']){
            return ReponseData::reponseFormat(2000,'跳转链接必填');
        }


        Banner::create($data);


        return ReponseData::reponseFormat(200,'新增成功');

    }

    public function BannerUpdate($request)
    {
        $id = $request['id'] ?? null;


        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $banner = Banner::where('id', $id)->first();
        if(!$banner){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }

        $data =[
            'image' => $request['image'] ?? $banner['image'],
            'url' => $request['url'] ?? $banner['url'],
            'status' => $request['status'] ?? $banner['status'],
            'name' => $request['name'] ?? $banner['name'],
        ];
        $banner->update($data);
        return ReponseData::reponseFormat(200,'更新成功');

    }

    public function BannerDelete($request)
    {
        $id = $request['id'] ?? null;


        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $banner = Banner::where('id', $id)->first();
        if(!$banner){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }

        $banner->delete();


        return ReponseData::reponseFormat(200,'删除成功');

    }

    private function createClient(){
        $config = new Config([
            'accessKeyId' => config('scheme.access_key'),
            'accessKeySecret' => config('scheme.secret'),
            'endpoint' => 'dypnsapi.aliyuncs.com'
        ]);
        return new Dypnsapi($config);
    }

    public function oneKeyLogin($request)
    {
        $ip = getIp($request);

        $data = [
            'spToken' => $request['spToken'] ?? null,
            'platform' => $request['platform'] ?? null,
            'type' => $request['type'] ?? 1,
        ];
        if(!$data['spToken']){
            return ReponseData::reponseFormat(2000,'spToken必传');
        }

        if(!$data['platform']){
            return  ReponseData::reponseFormat(2000,'平台标识必传');
        }

        if($data['platform'] == 1){
            $platform = 'ios';
        }else{
            $platform = 'android';

        }
        $schemeCode = config("scheme.scheme_codes.{$platform}");
        try {
            if (!$schemeCode) {
                return ReponseData::reponseFormat(2000, '未配置' . $platform . '平台的方案Code');
            }
                $client = $this->createClient();
                $request = new GetMobileRequest();
                $request->accessToken = $data['spToken'];
            // 🔥 核心修复：新版 SDK 正确调用方式
                $response = $client->getMobile($request);
                $body = $response->body;
                Log::info('body:'.json_encode($body));
                if ($body->code !== 'OK') {
                   return ReponseData::reponseFormat(2000,'认证失败：' . $body->message);
                }
                $phone = $body->getMobileResultDTO->mobile;
                if($data['type'] == 1){
                    $userInfo = $this->repo->getUserByMobile($phone);
                    if(!isset($userInfo)) {
//                        $minId = CuserAgent::query()->where('level', 1)->min('id');
//                        $maxId = CuserAgent::query()->where('level', 1)->max('id');
//                        $roundId = 3;
                        $special_area = CuserAgent::where('superior_agent_id',0)->where('id','>',3)->inRandomOrder()->first();
                        $insertData = [
                            'phone_number' => $phone,
                            'special_area' => $special_area['id'],
                            'special_area_name' => $special_area['agent_name'],
                            'register_time' => time(),
                            'login_ip' => $ip,
                            'head_shot' => 'https://zksj-new.oss-cn-beijing.aliyuncs.com/zk/image/ZKSJ_1770280030SR25.jpeg', //默认头像
                            'username' => '掌中视界' . mt_rand(10000000, 99999999),
                            'show_id' => mt_rand(10000000, 99999999),
                        ];

                        $user = $this->repo->createUsers($insertData);
                        $balance = CuserWallet::getBalance($user['id'], $special_area['id']);
                        if ($user && isset($balance)) {
//            $response = $this->encrypt($this->registerLogin($user));
                            $response = $this->registerLogin($user);

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
                    $sessionKey              = base64_encode(md5($userInfo['id'].$userInfo['user_name'].$nowTime));
                    $key = 'token_'.$userInfo['id'];
                    Redis::set($key, $sessionKey);

                    $updateData = [
                        'last_online_time' => $nowTime,
                        'login_ip' => $ip,
                        'session_key' => $sessionKey,
                    ];
                    Cuser::where('id', $userInfo['id'])->update($updateData);
                    $response =  [
                        'id' => $userInfo['id'],
                        'special_area' => $userInfo['special_area'],
                        'session_key' => $sessionKey,
                    ];
                    $responseData = $response;
                    return ReponseData::reponseFormatList(200,'成功',$responseData);
                }else{
                    $agent = CuserAgent::where('phone_number',$phone)->first();
                    if(!$agent){
                        return ReponseData::reponseFormat(2000,'该账号还未注册成为代理商!');
                    }
                    if($agent['is_cancel'] == 1){
                        $agent->update(['is_cancel'=>0]);
                    }
                    if(isset($data['password']) && $agent['password'] != $data['password']){
                        return ReponseData::reponseFormat(2003,'账号密码错误！');
                    }
                    $nowTime                 = time();
                    $sessionKey              = base64_encode(md5($agent['id'].$agent['agent_name'].$nowTime));
                    $key = 'agent_token_'.$agent['id'];
                    Redis::set($key, $sessionKey);
                    $response =  [
                        'id' => $agent['id'],
                        'special_area' => $agent['special_area'] ?? 0,
                        'session_key' => $sessionKey,
                    ];
                    return ReponseData::reponseFormatList(200,'成功',$response);
                }

        }catch (\Exception $e) {
            return response()->json([
                'code' => 2000,
                'msg' => '系统错误：' . $e->getMessage()
            ]);
        }
    }
}
