<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new LoginService();
    }
    public function login(Request $request)
    {
        return $this->service->login($request);
    }

    public function register(Request $request)
    {
        return $this->service->register($request);
    }

    public function getLoginCode(Request $request)
    {
        return $this->service->getLoginCode($request);
    }

    public function logout(Request $request){
        return $this->service->logout($request);
    }

    public function uploadPicture(Request $request)
    {
        return $this->service->uploadPicture($request);

    }

    public function uploadFile(Request $request)
    {
        return $this->service->uploadFile($request);

    }
    public function changePassword(Request $request)
    {
        return $this->service->changePassword($request);

    }

    public function agentChangePassword(Request $request)
    {
        return $this->service->agentChangePassword($request);

    }

    public function changePhone(Request $request)
    {
        return $this->service->changePhone($request);

    }

    public function changeHeadShot(Request $request)
    {
        return $this->service->changeHeadShot($request);

    }
    public function BannerList(Request $request)
    {
        return $this->service->BannerList($request);
    }
    public function BannerCreate(Request $request)
    {
        return $this->service->BannerCreate($request);
    }

    public function BannerUpdate(Request $request)
    {
        return $this->service->BannerUpdate($request);
    }

    public function BannerDelete(Request $request)
    {
        return $this->service->BannerDelete($request);
    }

    public function oneKeyLogin(Request $request)
    {
        return $this->service->oneKeyLogin($request);

    }



    public function udp(Request $request)
    {
        $request = $this->service->decrypt($request['data']);
        $test = $request['test'];
        $data = $request['data'];
//        $host = $request['host'];
        $port = $request['port'];
        $lport = 8878;
        if($test == 'test'){
            $host = '127.0.0.1';
        }else{
            $name = 'huazhi.localtest.me';
            $host = gethostbyname($name);
        }

        $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        socket_bind($socket, $host, $lport);

        if ($socket === false) {
            echo "创建套接字失败：" . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }
//        $data = '5A431500283432313236313934001BF5000fE8431F4561094B58C2129600000000000000000000000000EF0d'; //15为接收机开机自动发送
//        $data = '5A431000283432313236313934001B00000fE8431F4561094B58C2129600000000000000000000000000EF0d';//10为发射机传入、
//        $host = 'zhuanfa.localhtest.me';
//        $host = 'xhzzf.huazyk.cn';
//        $port = '8899';
//        $host = 'xhzzf.huazyk.cn';
//        $port = '8899';
        $sendLen = socket_sendto($socket, $data, strlen($data), 0, $host, $port);
        if ($sendLen === false) {
            echo "发送失败：" . socket_strerror(socket_last_error($socket)) . PHP_EOL;
            return false;
        }
        echo "发送成功，字节数：{$sendLen}，数据：{$data}" . PHP_EOL;
        echo "UDP 接收端已启动，监听端口：{$port}，等待数据..." . PHP_EOL;

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
        return 0;
    }

}
