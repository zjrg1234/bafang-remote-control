<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\IndexService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    //

    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new IndexService();
    }

    public function startupPage(Request $request)
    {
        return $this->service->startupPage($request);
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }
    public function venueDetail(Request $request)
    {
        return $this->service->venueDetail($request);
    }
    public function mine(Request $request)
    {
        return $this->service->mine($request);
    }
    public function specialList(Request $request)
    {
        return $this->service->specialList($request);
    }
    public function changeSpecial(Request $request)
    {
        return $this->service->changeSpecial($request);
    }
    public function reservationList(Request $request)
    {
        return $this->service->reservationList($request);
    }

    public function drivingRecord(Request $request)
    {
        return $this->service->drivingRecord($request);
    }
    public function walletList(Request $request)
    {
        return $this->service->walletList($request);

    }
    public function wechatDeposit(Request $request)
    {
        return $this->service->wechatDeposit($request);
    }
    public function wechatNotify(Request $request)
    {
        return $this->service->wechatNotify($request);

    }
    public function alipayDeposit(Request $request)
    {
        return $this->service->alipayDeposit($request);
    }

    public function feedBack(Request $request)
    {
        return $this->service->feedBack($request);

    }
    public function deactivate(Request $request)
    {
        return $this->service->deactivate($request);

    }

    public function drivingProtocol(Request $request)
    {
        return $this->service->drivingProtocol($request);

    }

    public function complainList(Request $request)
    {
        return $this->service->complainList($request);

    }

    public function changeName(Request $request)
    {
        return $this->service->changeName($request);

    }

    public function accountCancel(Request $request)
    {
        return $this->service->accountCancel($request);

    }

    public function getTitle(Request $request)
    {
        return $this->service->getTitle($request);

    }

    public function complain(Request $request)
    {
        return $this->service->complain($request);

    }

    public function startDriving(Request $request)
    {
        return  $this->service->startDriving($request);
    }

    public function reservation(Request $request)
    {
        return  $this->service->reservation($request);

    }
    public function cancelReservation(Request $request)
    {
        return  $this->service->cancelReservation($request);

    }

    public function depositList(Request $request)
    {
        return  $this->service->depositList($request);

    }

    public function depositActivityList(Request $request)
    {
        return  $this->service->depositActivityList($request);
    }

    public function alipayNotify(Request $request)
    {
        return $this->service->alipayNotify($request);
    }

    public function chackUnusualReservation(Request $request)
    {
        return  $this->service->chackUnusualReservation($request);

    }
    public function chackStartDriving(Request $request)
    {
        return  $this->service->chackStartDriving($request);

    }

    public function Banner(Request $request)
    {
        return  $this->service->Banner($request);

    }

    public function AppVersion(Request $request)
    {
        return $this->service->AppVersion($request);
    }

    public function lockDriving(Request $request)
    {
        return $this->service->lockDriving($request);

    }
}
