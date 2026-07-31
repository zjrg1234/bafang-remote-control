<?php

namespace App\Http\Middleware;

use App\Models\AppVersion;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\ReponseData;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {



        if(env('APP_ENV')=='local' && env('ISBACKENDTOKEN')=='NONONO'){
            return $next($request);
        }
        if(empty($_SERVER['HTTP_AUTHORIZATION']) && empty( $request->header('token'))){
            return ReponseData::reponseFormat(401,'登录失效!');
        }
        $session_key = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->header('token');
        $aesKey = config('aes.aes_key');
//        $request = json_decode(aesDecrypt($request['data'],'aes-128-ecb',$aesKey),true);
        if($request->server('REQUEST_URI') == '/api/reset/default/channel' || $request->server('REQUEST_URI') == '/api/vehicle/detail/save' || $request->server('REQUEST_URI') == '/api/vehicle/detail' || $request->server('REQUEST_URI') == '/api/chack/start/driving' || $request->server('REQUEST_URI') == '/api/user/chack/stop/driving')
        {
            return $next($request);
        }
        $platform = $request->header('platform') ?? '';
        $request_version = $request->header('versionCode') ?? '';

        if($platform && $platform == 'ZZSJ_iOS'){
            $app_version = AppVersion::where(['type'=>1,'status'=>1])->first();
            if($app_version['forced_updating'] == 1 && $request_version != $app_version['version_mark']){
                return ReponseData::reponseFormat(2000,'请更新最新版app哦!');
            }
            if($request->server('REQUEST_URI') == '/api/user/start/driving' || $request->server('REQUEST_URI') == '/api/user/reservation'){
                return ReponseData::reponseFormat(2000,'请更新最新版app哦!');
            }
        }
        if ($platform && $platform == 'ZZSJ_Android'){
            $app_version = AppVersion::where(['type'=>2,'status'=>1])->first();
            if($app_version['forced_updating'] == 1 && $request_version != $app_version['version_mark']){
                return ReponseData::reponseFormat(2000,'请更新最新版app哦!');
            }
            if($request->server('REQUEST_URI') == '/api/user/start/driving' || $request->server('REQUEST_URI') == '/api/user/reservation'){
                return ReponseData::reponseFormat(2000,'请更新最新版app哦!');
            }
        }


        if (!isset($session_key)) {
            return ReponseData::reponseFormat(401, '登录失效!');
        }
        Log::info('request_token:   '.$session_key);
        $uid = $request['uid'] ?? null;
        $agent_id = $request['agent_id'] ?? null;
        if($uid){
            $key = 'token_' . $uid;
            $user = Cuser::find($uid);
            if(!$user) {
                return ReponseData::reponseFormat(401, '登录失效!');
            }
            $userToken = Redis::get($key);

            if ($userToken != $session_key) {
                return ReponseData::reponseFormat(401, '登录失效!');
            }
            if($user['is_cancel'] == 1){
                return ReponseData::reponseFormat(401,'账户已注销');
            }
            if($user['is_locked'] == 1){
                return ReponseData::reponseFormat(401,'账户已封号');
            }
        }else if($agent_id){
            $key = 'agent_token_'.$request['agent_id'];
            $user = CuserAgent::find($agent_id);
            if(!$user) {
                return ReponseData::reponseFormat(401, '登录失效!');
            }
            $userToken = Redis::get($key);
//            if ($userToken != $session_key) {
//                return ReponseData::reponseFormat(401, '登录失效!');
//            }
            if($user['is_frozen'] == 1){
                return ReponseData::reponseFormat(401,'账户已冻结');
            }
            if($user['is_delete'] == 1){
                return ReponseData::reponseFormat(401,'账户已删除');
            }
        }else{
            return ReponseData::reponseFormat(401, '登录失效!');
        }
        if (!$userToken) {
            return ReponseData::reponseFormat(401, '登录失效!');
        }


//        if($user['is_locket'] == 1){
//            return ReponseData::reponseFormat(130, '该账户已被锁定，请联系管理员!');
//        }
        return $next($request);
    }
}
