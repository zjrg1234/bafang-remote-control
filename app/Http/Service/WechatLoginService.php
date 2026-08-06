<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class WechatLoginService
{


    /**
     * 获取小程序access_token
     */
    protected function getMiniAccessToken()
    {
        $redisKey = 'mini:access_token';
        $token = Redis::get($redisKey);
        if ($token) {
            return $token;
        }

        $appId = env('WECHAT_MINI_APPID');
        $secret = env('WECHAT_MINI_SECRET');
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secret}";
        $resp = Http::get($url)->json();

        if (isset($resp['errcode'])) {
            throw new \Exception("获取access_token失败：" . $resp['errmsg']);
        }
        // 有效期7200秒，缓存设置7000，预留缓冲
        Redis::setex($redisKey, 7000, $resp['access_token']);
        return $resp['access_token'];
    }

    /**
     * 根据前端code 获取手机号
     * @param string $phoneCode getPhoneNumber返回的code
     * @return string 手机号purePhoneNumber
     */
    public function getWxMobile(string $phoneCode): string
    {
        $accessToken = $this->getMiniAccessToken();
        $url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$accessToken}";

        $resp = Http::postJson($url, [
            'code' => $phoneCode
        ])->json();

        if ($resp['errcode'] !== 0) {
            throw new \Exception("获取手机号失败[{$resp['errcode']}]：" . $resp['errmsg']);
        }
        return $resp['phone_info']['purePhoneNumber'];
    }


}
