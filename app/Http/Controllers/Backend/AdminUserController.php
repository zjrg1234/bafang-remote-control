<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Service\UserService;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new UserService();
    }
    public function list(Request $request)
    {
        return  $this->service->list($request);
    }

    public function details(Request $request)
    {
        return  $this->service->details($request);
    }

    public function modifyBalance(Request $request)
    {
        return  $this->service->modifyBalance($request);

    }
    public function modifyEnergy(Request $request)
    {
        return  $this->service->modifyEnergy($request);

    }
    public function changePassword(Request $request){
        return $this->service->changePassword($request);
    }
    public function changeBalance(Request $request){
        return $this->service->changeBalance($request);
    }

    public function changeEnergy(Request $request){
        return $this->service->changeEnergy($request);
    }

    public function frozen(Request $request)
    {
        return  $this->service->frozen($request);

    }
    public function delete(Request $request)
    {
        return  $this->service->delete($request);
    }

    public function specialList(Request $request)
    {
        return  $this->service->specialList($request);

    }

    public function changeBalanceLog(Request $request)
    {
        return  $this->service->changeBalanceLog($request);
    }
    public function changeEnergyLog(Request $request)
    {
        return  $this->service->changeEnergyLog($request);
    }

    public function walletList(Request $request)
    {
        return  $this->service->walletList($request);

    }

    public function walletDetail(Request $request)
    {
        return  $this->service->walletDetail($request);

    }


    public function changeWalletBalance(Request $request)
    {
        return  $this->service->changeWalletBalance($request);

    }

    public function updateScreenshot(Request $request)
    {
        return  $this->service->updateScreenshot($request);

    }
}
