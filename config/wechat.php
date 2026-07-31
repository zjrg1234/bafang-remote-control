<?php
return [
    'mchid'       => env('WECHATPAY_MCHID'),
    'appid'       => env('WECHATPAY_APPID'),
    'apiv3_key'   => env('WECHATPAY_APIV3_KEY'),
    'pub_key_id'  => env('WECHATPAY_PUB_KEY_ID'),
    'platform' => env('WECHATPAY_PLATFORM_SERIAL'),
    // 利用 storage_path 安全加载本地密钥文件
    'merchant_cert' => storage_path('wechat/apiclient_cert.pem'),
    'merchant_key'  => storage_path('wechat/apiclient_key.pem'),
    'wechat_pub_key'=> storage_path('wechat/pub_key.pem'),
];
