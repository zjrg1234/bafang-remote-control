<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAesEntry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(env('APP_ENV')=='local' && env('ISBACKENDTOKEN')=='NONONO'){
            $data = $request->all();
            $request['data'] = aesEncrypt(json_encode($data),'aes-128-ecb',config('aes.aes_key'));
            return $next($request);
        }
        return $next($request);
    }
}
