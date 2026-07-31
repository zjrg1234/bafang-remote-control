<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class testUdpSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-udp-send';

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
//        $data = Redis::get('42126194_receiver');
//        dd($data);
//        //42126195 发射id
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === false) {
            echo "创建套接字失败：" . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }
        $data = '5A431500283432313236313934001BF5000fE8431F4561094B58C2129600000000000000000000000000EF0d'; //15为接收机开机自动发送
//        $data = '5A431000283432313236313935001B00000fE8431F4561094B58C2129600000000000000000000000000EF0d';//10为发射机传入、
        $host = 'zksjtest.zksjyk.cn';
//        $host = '192.168.2.154';
////        $host = '43.240.193.30';
        $port = '8899';
//        121.226.170.153:8898
//        $host = '121.226.170.153';
        $sendLen = socket_sendto($socket, $data, strlen($data), 0, $host,$port);
        if ($sendLen === false) {
            echo "发送失败：" . socket_strerror(socket_last_error($socket)) . PHP_EOL;
            return false;
        }
        echo "发送成功，字节数：{$sendLen}，数据：{$data}" . PHP_EOL;
        sleep(1);
        return 0;
    }
}
