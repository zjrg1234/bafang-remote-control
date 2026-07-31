<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ReponseData;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tymon\JWTAuth\Facades\JWTAuth;
use function Symfony\Component\Translation\t;

class AdminController extends Controller
{

    public function login(Request $request)
    {
//        $password = Hash::make('123456');  //密码生成

        $login = [
            'username'=>$request->input('username'),
            'password'=>$request->input('password'),
        ];
//        $code = $request->input('code');
        if(empty($login['username']) || empty($login['password'])){
            return ReponseData::reponseFormat(2000,'请输入账号或密码！');
        }
        $user = AdminUser::where(['username' => $login['username']])->first();
        if(!$user){
            return ReponseData::reponseFormat(2001,'未找到该登录用户！');
        }


        //方式一
//        $token = JWTAuth::fromUser($login);
        //方式二
//        $token = auth('api')->login($user);

        //方式三、下面这种方式必须使用密码登录
        $token = Auth::guard('api')->attempt($login);
        if(!$token){
            return ReponseData::reponseFormat(2002,'账号密码错误！');
        }
        /**谷歌验证暂时取掉先
        $ga = new Google2FA();
        if($user['secret'] == ''){
            $length = env('GOOGLE_CODE_LENGTH');
            $google2fa_secret = $ga->generateSecretKey($length);
            $company = $login['username'];
            $email = $login['username'].'@gmail.com';
            $url = $ga->getQRCodeUrl($company, $email, $google2fa_secret);
            $path = public_path('Img/'.$google2fa_secret.'.svg');
            $imgPath = 'Img/'.$google2fa_secret.'.svg';
            QrCode::size(200)->generate($url,$path);
            $user['secret'] = $google2fa_secret;
            $user->save();
            return ReponseData::reponseFormatList(2003,'请先完成谷歌认证',['img'=>env('APP_URL').$imgPath]);
        }
        if($code){
            $verifyKey = $ga->verifyKey($user['secret'],$code);
            if(!$verifyKey){
                return ReponseData::reponseFormat(2004,'验证不通过！请重新输入code');
            }
        }else{
            return ReponseData::reponseFormat(2004,'谷歌验证码必填!');

        }*/
        $resp = [
            'uid'=>$user['id'],
            'username'=>$user['username'],
            'token'=> $token,
        ];

        return ReponseData::reponseFormatList(200,'登录成功',$resp);

    }

//    public function register(Request $request){。//注册暂时先不要
//        $register = [
//            'username'=>$request->input('username'),
//            'password'=>Hash::make($request->input('password')),
//        ];
//        if(empty($register['username']) || empty($register['password'])){
//            return ReponseData::reponseFormat(400,'请输入账号或密码！');
//        }
//        $user = AdminUser::where(['username' => $register['username']])->first();
//
//        if($user){
//            return ReponseData::reponseFormat(400,'该用户已经存在！');
//        }
//
//        AdminUser::create($register);
//
//        return ReponseData::reponseFormat(200,'创建用户成功！');
//
//    }

    public function logout(Request $request){
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        $type = JWTAuth::setToken($token)->check();
        if(!$type){
            return ReponseData::reponseFormat(200,'退出成功！');
        }
        JWTAuth::setToken($token)->invalidate();
        return ReponseData::reponseFormat(200,'退出成功！');

    }

    public function resetGoogle2fa(Request $request)
    {
        $id = $request->input('id');

        if(!$id){
            return ReponseData::reponseFormat(500,'id必传');
        }

        $user = AdminUser::where('id', $id)->first();
        if(!$user){
            return ReponseData::reponseFormat(500,'未找到该用户');
        }
        $user->secret = '';
        $user->save();

        return ReponseData::reponseFormat(200,'重置成功');
    }
}
