<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class AlipayNativeService
{
    // 配置参数
    private $config;

    public function __construct()
    {
        $this->config = config('alipay');
    }

    /**
     * 核心：生成RSA2签名（支付宝规范）
     * @param array $params 待签名参数
     * @return string 签名结果
     */
    private function generateSign(array $params): string
    {
        // 1. 过滤空值、sign、sign_type参数
        $params = array_filter($params, function ($value) {
            return $value !== '' && $value !== null;
        });
        unset($params['sign']);

        // 2. 按ASCII码升序排序参数（关键！签名失败的常见原因）
        ksort($params);
        // 3. 拼接成key=value&key=value格式
        $stringToSign = '';
        foreach ($params as $k => $v) {
            $stringToSign = $stringToSign. $k . '=' . $v . '&';
        }
        $stringToSign = Str::substr($stringToSign, 0, -1);
//        $stringToSign = http_build_query($params);
        $key = str_replace([' ', "\n", "\r", "\t"], '', env('ALIYUN_PRI_KEY'));
        // 4. RSA2签名（SHA256WithRSA）
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($key, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($stringToSign, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        // 5. Base64编码返回
        return base64_encode($sign);
    }

    /**
     * 验证支付宝签名（异步通知/同步返回）
     * @param array $params 支付宝返回的参数
     * @return bool 验签结果
     */
    public function verifySign(array $params): bool
    {
        // 1. 提取签名并删除原参数中的sign
        $sign = $params['sign'] ?? '';
        unset($params['sign']);
        $params['fund_bill_list'] = json_encode($params['fund_bill_list'],JSON_UNESCAPED_UNICODE);
        // 2. 过滤空值、按ASCII升序排序
        $params = array_filter($params);
        ksort($params);

        // 3. 拼接成key=value&key=value格式
        $stringToVerify = '';
        foreach ($params as $k => $v) {
            $stringToVerify = $stringToVerify. $k . '=' . $v . '&';
        }
        $stringToVerify = Str::substr($stringToVerify, 0, -1);

        // 4. 加载支付宝公钥
        $alipayPublicKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap(env('ALIPAY_PUBLIC_KEY'), 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        // 5. 验签
        $result = openssl_verify($stringToVerify, base64_decode($sign), $alipayPublicKey, OPENSSL_ALGO_SHA256);
        dd($result);
        return $result === 1;
    }

    /**
     * 调用支付宝APP支付统一下单接口，生成orderStr（给APP端）
     * @param array $order 订单参数（out_trade_no, total_amount, subject）
     * @return string 支付参数orderStr
     * @throws \Exception
     */
    public function createAppOrder(array $order): string
    {
        // 1. 构造请求参数
        $params = [
            'app_id' => env('ALI_ID'),
            'biz_content' => json_encode([
                'out_trade_no' => $order['order_no'],
//                'total_amount' => $order['amount'].'.00',
                'total_amount' => $order['amount'],
                'subject' => $order['subject'],
                'timeout_express' => '15m', // 订单15分钟过期
                'product_code' => 'QUICK_MSECURITY_PAY', // APP支付固定值
            ], JSON_UNESCAPED_UNICODE),
            'format' => 'JSON',
            'charset' => 'UTF-8',
            'method' => 'alipay.trade.app.pay', // APP支付接口
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'notify_url'=>env('ALIPAY_NOTIFY_URL'),
//            'version' => '1.0',
        ];

        // 2. 生成签名
        $sign = $this->generateSign($params);

        $orderStr = http_build_query($params);
//        $params['sign'] = urlencode($sign);
        // 3. 拼接成APP端需要的orderStr（key=value&key=value）
//        ksort($params);
//        $orderStr = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
//        $orderStr1 = http_build_query($params);
         $orderStr = $orderStr.'&sign=' . urlencode($sign);
//        dd($orderStr, $orderStr1);
        return $orderStr;
    }

    /**
     * 发送HTTP请求到支付宝接口（备用，如查询订单状态）
     * @param array $params 请求参数
     * @return array 支付宝返回结果
     * @throws \Exception
     */
    public function requestAlipayApi(array $params): array
    {
        $params['sign'] = $this->generateSign($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['gateway_url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 沙箱环境可关闭，生产建议开启
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('请求支付宝接口失败：' . curl_error($ch));
        }
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
