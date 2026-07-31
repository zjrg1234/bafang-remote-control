<?php

return  [
    'access_key' => env('ALIYUN_OSS_ACCESS_KEY_ID'),
    'secret' => env('ALIYUN_OSS_ACCESS_KEY_SECRET'),
    'scheme_codes' => [
        'android' => env('ALIYUN_SCHEME_CODE_ANDROID'),
        'ios' => env('ALIYUN_SCHEME_CODE_IOS'),
    ],

];
