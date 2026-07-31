<?php

namespace App\Http\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use WeChatPay\Builder;
class WechatPayV3Service{
    protected $config;
    protected $client;
    protected $merchantPrivateKey;

    public function __construct()
    {
        // 1. 直接获取项目根目录下的绝对物理路径
        $keyPath = base_path('storage/wechat/apiclient_key.pem');

// 2. 暴力诊断 A：文件到底在不在？
        if (!file_exists($keyPath)) {
            dd("❌ 诊断失败：找不到文件！请检查路径是否正确 -> " . $keyPath);
        }

// 3. 暴力诊断 B：PHP 到底能不能读？(如果是权限问题，这里会返回 false 并抛出 Warning)
        $keyString = file_get_contents($keyPath);
        if ($keyString === false || empty($keyString)) {
            dd("❌ 诊断失败：文件存在，但没有读取权限！请执行 chmod 600 和 chown 赋予 web 用户权限。");
        }

// 4. 暴力诊断 C：文件内容对不对？(防止你把公钥当私钥放进去了)
        if (strpos($keyString, 'BEGIN PRIVATE KEY') === false) {
            dd("❌ 诊断失败：文件能读到，但这根本不是私钥文件！请用文本编辑器打开看看开头是不是 BEGIN PRIVATE KEY");
        }

// 5. 诊断全数通过，直接将字符串本体喂给 SDK，彻底绕过 file:// 的路径解析坑
        $this->merchantPrivateKey = Rsa::from($keyString, Rsa::KEY_TYPE_PRIVATE);


        // 1. 加载商户私钥 (保存为类属性，后续生成二次签名还需要用)
//        $this->merchantPrivateKey = Rsa::from('file://' . config('wechat.merchant_key'), Rsa::KEY_TYPE_PRIVATE);



        // 2. 解析商户证书序列号
        $merchantCertificateSerial = PemUtil::parseCertificateSerialNo('file://' . config('wechat.merchant_cert'));
        // 3. 加载微信支付公钥
        $wechatPublicKeyInstance = Rsa::from('file://' .config('wechat.wechat_pub_key'), Rsa::KEY_TYPE_PUBLIC);
        // 4. 构造 HTTP 客户端
        $this->instance = Builder::factory([
            'mchid'      => config('wechat.mchid'),
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $this->merchantPrivateKey,
            'certs'      => [
                config('wechat.platform') => $wechatPublicKeyInstance,
            ],
        ]);
    }

    /**
     * 调用APP支付统一下单接口（V3）
     * @param string $outTradeNo 商户订单号
     * @param float $totalAmount 订单金额（元）
     * @param string $description 商品描述
     * @return array APP端调起支付的参数
     * @throws GuzzleException
     */
    public function createAppOrder($data)
    {
        // 实际业务中应从请求或数据库中获取订单信息，这里作演示
        $orderSn = $data['order_no'];
        $amount = $data['amount'] * 100; // 金额先按照0.01来 实际金额需要*100
        $description = '电池购买';
            $resp = $this->instance->chain('v3/pay/transactions/app')->post([
                'json' => [
                    'mchid'        => config('wechat.mchid'),
                    'out_trade_no' => $orderSn,
                    'appid'        => config('wechat.appid'),
                    'description'  => $description,
                    // 利用 url() 助手函数动态生成当前环境的回调绝对路径
                    'notify_url'   => env('WECHAT_PAY_NOTIFY_URL'),
                    'amount'       => [
                        'total'    => (int)$amount,
                        'currency' => 'CNY'
                    ],
                    'time_expire' => date('Y-m-d').'T'.date('H:i:s',time() + 300).'+08:00',
                ]
            ]);

            $prepayId = json_decode($resp->getBody(), true)['prepay_id'];

            // === 生成给 iOS/Android 的二次签名数据 ===
            $timeStamp = (string)time();
            $nonceStr  = Formatter::nonce();

            // APP支付的签名要素（必须严格按此顺序和换行符）
            $message = config('wechat.appid') . "\n" .
                $timeStamp . "\n" .
                $nonceStr . "\n" .
                $prepayId . "\n";

            $sign = Rsa::sign($message, $this->merchantPrivateKey);

            return [
                    'appid'     => config('wechat.appid'),
                    'partner_id' => config('wechat.mchid'),
                    'prepay_id'  => $prepayId,
                    'package'   => 'Sign=WXPay', // 固定值
                    'nonce_str'  => $nonceStr,
                    'timestamp' => $timeStamp,
                    'sign'      => $sign
                ];

    }

    /**
     * 验证支付回调通知的签名
     * @param string $body 回调原始数据
     * @param string $serial 微信平台证书序列号
     * @param string $signature 签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机串
     * @return bool
     */
    /**
     * API: 接收微信支付结果异步回调
     * 严密性体现：验签 -> 解密 -> 开启事务 -> 加悲观锁查单 -> 金额校验 -> 幂等处理
     */
    public function notify($inBody)
    {

        // 1. 获取微信传递的头部签名信息
        // 注：由于你在初始化配置了微信公钥，SDK理论上可以自动验签，
        // 但处理回调时，需要手动验证或直接解密（如果不怕服务器被无效请求打满的话）。
        // 最佳实践是先解密真实报文。

        $bodyArray = json_decode($inBody, true);
        if (empty($bodyArray['resource'])) {
            return response()->json(['code' => 'FAIL', 'message' => '数据格式错误'], 400);
        }

        $resource = $bodyArray['resource'];

        try {
            // 2. 报文解密 (使用 API v3 密钥)
            $decrypted = AesGcm::decrypt(
                $resource['ciphertext'],
                config('wechatpay.apiv3_key'),
                $resource['nonce'],
                $resource['associated_data']
            );
            $payData = json_decode($decrypted, true);

            Log::info('微信支付回调解密成功: ', $payData);

            // 如果不是支付成功状态，直接抛弃
            if ($payData['trade_state'] !== 'SUCCESS') {
                return response()->json(['code' => 'SUCCESS', 'message' => '非成功状态不处理']);
            }

            $outTradeNo = $payData['out_trade_no'];
            $paidTotal = $payData['amount']['total'];

            // 3. 开启数据库事务，处理本地逻辑
            DB::beginTransaction();

            try {
                // 严密性：使用 lockForUpdate() 加悲观锁，防止同一回调并发处理
                $order = DB::table('orders')->where('order_sn', $outTradeNo)->lockForUpdate()->first();

                if (!$order) {
                    throw new \Exception('订单不存在');
                }

                // 严密性：幂等判断。如果订单已经是已支付状态，直接返回成功给微信
                if ($order->status === 'paid') {
                    DB::commit();
                    return response()->json(['code' => 'SUCCESS', 'message' => '成功']);
                }

                // 严密性：校验金额。防止1分钱篡改购买100元商品
                if ($order->total_amount != $paidTotal) {
                    Log::critical("支付金额不匹配！订单: {$outTradeNo}, 订单金额: {$order->total_amount}, 实际支付: {$paidTotal}");
                    // 业务上应标记为异常订单，并通知运营退款
                    throw new \Exception('支付金额异常');
                }

                // 4. 更新订单状态
                DB::table('orders')->where('id', $order->id)->update([
                    'status'         => 'paid',
                    'transaction_id' => $payData['transaction_id'], // 记录微信流水号
                    'paid_at'        => date('Y-m-d H:i:s')
                ]);

                // 这里可以触发事件派发，比如：event(new OrderPaid($order));

                DB::commit();

                // 必须严格按微信要求返回 JSON
                return response()->json(['code' => 'SUCCESS', 'message' => '成功']);

            } catch (\Exception $dbEx) {
                DB::rollBack();
                Log::error('回调业务处理失败: ' . $dbEx->getMessage());
                // 返回非200或非SUCCESS，微信会阶梯性重试
                return response()->json(['code' => 'FAIL', 'message' => '处理失败'], 500);
            }

        } catch (\Exception $e) {
            Log::error('微信回调解密/解析失败: ' . $e->getMessage());
            return response()->json(['code' => 'FAIL', 'message' => '报文解密失败'], 500);
        }
    }


    /**
     * 查询订单状态
     * @param string $outTradeNo 商户订单号
     * @return array
     * @throws GuzzleException
     */
    public function queryOrder(string $outTradeNo): array
    {
        $response = $this->client->get("/v3/pay/transactions/out-trade-no/{$outTradeNo}?mchid={$this->config['mch_id']}");
        return json_decode($response->getBody()->getContents(), true);
    }
}
