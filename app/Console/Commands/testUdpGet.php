<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class testUdpGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-udp-get {test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $apiV3Key = bin2hex(random_bytes(16)); // random_bytes生成16字节随机数，bin2hex转成32位16进制字符串
        echo "你的32位APIv3密钥：" . $apiV3Key . "\n";
        dd($apiV3Key);
        $data = 'ZC28001308&&98930212111203928112977'; //心跳

        $port = 8898;
        $lport = 8898;
        $lhost = '0.0.0.0';
//        $name = 'hthz.huazyk.cn';
//        $host = gethostbyname($name);
        $data = '5A431600283432313236313934001BF5000fE8431F4561094B58C2129600000000000000000000000000EF0d'; //16为心跳开机自动发送
        $host = 'zhuanfa.localhtest.me';

//        $host = '192.168.2.154';
////        $host = '43.240.193.30';
        $port = '8899';
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($socket, $lhost, $lport);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        if ($socket === false) {
            echo "创建套接字失败：" . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }
//        for ($i =1;$i<=5;$i++) {
            $sendLen = socket_sendto($socket, $data, strlen($data), 0, $host, $port);
            if ($sendLen === false) {
                echo "发送失败：" . socket_strerror(socket_last_error($socket)) . PHP_EOL;
                return false;
            }
            echo "发送成功，字节数：{$sendLen}，数据：{$data}" . PHP_EOL;
//        }

        // 4. 动态获取实际监听的 IP 和端口（重点！）
        if (!socket_getsockname($socket, $actualListenIp, $actualListenPort)) {
            echo "获取监听地址失败：" . $socket;
        }

        // 输出监听信息（实际绑定的地址，而非初始配置）
        echo "✅ UDP 服务已启动\n";
        echo "📡 实际监听地址：{$actualListenIp}:{$actualListenPort}\n";
        echo "📱 同一局域网设备可通过该地址发送数据（UDP 协议）\n";
        echo "⌛ 等待数据...\n\n";
// 循环接收数据
        while (true) {
            $data = '';
            $clientIp = '';
            $clientPort = 0;

            // 接收数据（缓冲区大小 1024 字节，可调整）
            $recvLength = socket_recvfrom(
                $socket,
                $data,
                1024,
                0,
                $clientIp,
                $clientPort
            );

            if ($recvLength > 0) {
                echo "收到数据：来自 {$clientIp}:{$clientPort}，数据：{$data}，字节数：{$recvLength}" . PHP_EOL;
            }
        }
        socket_close($socket);
    }
}
