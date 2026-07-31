<?php

namespace App\Http\Middleware;

use App\Models\ReponseData;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(env('APP_ENV')=='local' && env('ISBACKENDTOKEN')=='NONONO'){
            return $next($request);
        }

        $prefix =  env('PREFIX','tb');
        if(empty($_SERVER['HTTP_AUTHORIZATION'])){
            return ReponseData::reponseFormat(401,'token必传!');
        }
        //校验token是否有效
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        $type = JWTAuth::setToken($token)->check();
        if(!$type){
           return ReponseData::reponseFormat(401,'token认证失败或已过期 请重新确认!');
        }
//        $user = JWTAuth::setToken($token)->authenticate();
//        $request['auth_uid'] = $user['id'];
        return $next($request);
    }
}
