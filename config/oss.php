<?php

return [
    'access_key_id'     => env('ALIYUN_OSS_ACCESS_KEY_ID'),
    'access_key_secret' => env('ALIYUN_OSS_ACCESS_KEY_SECRET'),
    'bucket'            => env('ALIYUN_OSS_BUCKET'),
    'endpoint'          => env('ALIYUN_OSS_ENDPOINT'),
    'is_cname'          => env('ALIYUN_OSS_IS_CNAME', false),
    'openai_sign'       => env('ALIYUN_OSS_OPENAI_SIGN', false),
];
