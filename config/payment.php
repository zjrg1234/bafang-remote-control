<?php

return [
    'alipay' => [
        'url'  => env('ALIPAY_URL', 'https://openapi.alipay.com/gateway.do'),
        'dev_url'  => env('DEV_ALIPAY_URL', 'https://openapi.alipaydev.com/gateway.do'),
        'app_id'  => env('ALIPAY_APP_ID', '9021000158668592'),
        'notify_url'  => env('ALIPAY_NOTIFY_URL', ''),
        'return_url'  => env('ALIPAY_RETURN_URL', ''),
    ],
    'wechat' => [
        'url'  => env('WECHAT_URL', 'https://api.mch.weixin.qq.com'),
        'mah_id' => env('WECHAT_MAH_ID', ''),
        'app_id' => env('WECHAT_APP_ID', ''),
        'notify_url'  => env('WECHAT_NOTIFY_URL', '/api/wechat/wechat/notify'),
        'api_v3_key' => env('WECHAT_API_V3_KEY', ''),
        'meh_cert_path' => env('WECHAT_API_V3_CERT_PATH', ''), //公钥
        'meh_key_path' => env('WECHAT_API_V3_KEY_PATH', ''),//私钥
        'v3_payment_path' => env('WECHAT_API_V3_PAYMENT_PATH', '/v3/pay/transactions/app'),
        'time_expire' => env('WECHAT_API_V3_TIME_EXP', '60'),
    ],
];
