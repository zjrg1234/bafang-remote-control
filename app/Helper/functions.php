<?php

function getIp(\Illuminate\Http\Request $request)
{
    if (!$request) {
        return "";
    }
    if ($request->header('HTTP_CDN_LOOP') && $request->header('HTTP_CF_CONNECTING_IP')) {
        return $request->header('HTTP_CF_CONNECTING_IP');
    }
    if ($request->hasHeader("x-forwarded-for")) {
        $ip = $request->header("x-forwarded-for", "");
        if ($ip) {
            $ips = explode(',', $ip);
            return $ips[0];
        }
    } else {
        \Illuminate\Support\Facades\Log::info("header:".json_encode($request->headers));
    }
    return $request->getClientIp();
}

/**
 * method: ['aes-128-ecb','']
 * @param $data
 * @method
 * @param $key
 * @return string
 */
function aesEncrypt($data, $method, $key) : string {
    $method = strtoupper($method);
    $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_PKCS1_PADDING, '');
    return base64_encode($encrypted);
}

function aesDecrypt($data,$method ,$key) : string {
    return openssl_decrypt(base64_decode($data), $method, $key, OPENSSL_PKCS1_PADDING);
}

function getIpRaw(): string
{
    if ('cli' == php_sapi_name()) {
        return '127.0.0.114';
    }
    if (isset($_SERVER['HTTP_CDN_LOOP']) && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }

    if (isset($_SERVER['X-FORWARDED-FOR'])) {
        $ips = explode(" ", $_SERVER['X-FORWARDED-FOR']);
        return $ips[0];
    }

    return $_SERVER['REMOTE_ADDR'];
}

function orderNo(string $prefix) : string {
    $now = \Carbon\Carbon::now();
    $time = $now->format('YmdHisv');
    $rand = readableRand(4);
    return $prefix.$time.$rand;
}

/**
 * 去掉 0, 1, i, l , o, u ,v
 */
function readableRand($num) { //  不超过 29位
    $chars = 'ABCDEFGHJKMNPQRSTWXYZ23456789';
    $len = strlen($chars);
    $ret = '';
    for (;$num > 0; $num--) {
        $ret .= $chars[mt_rand(0, $len-1)];
    }

    return $ret;
}
