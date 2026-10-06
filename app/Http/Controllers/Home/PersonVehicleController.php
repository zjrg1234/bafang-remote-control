<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\PersonVehicleService;
use Illuminate\Http\Request;

class PersonVehicleController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new PersonVehicleService();
    }
    public function vehicleList(Request $request)
    {
        return  $this->service->vehicleList($request);
    }


    public function deleteVehicle(Request $request)
    {
        return  $this->service->deleteVehicle($request);
    }


    public function addVehicle(Request $request)
    {
        return $this->service->addVehicle($request);

    }
    public function vehicleDetail(Request $request){
        return $this->service->vehicleDetail($request);
    }

    public function vehicleDetailSave(Request $request)
    {
        return $this->service->vehicleDetailSave($request);
    }
    public function updateVehicle(Request $request)
    {
        return $this->service->updateVehicle($request);

    }
    public function vehicleDetailReset(Request $request)
    {
        return $this->service->vehicleDetailReset($request);

    }
    public function startDriving(Request $request)
    {
        return $this->service->startDriving($request);

    }
    public function bindAppLoginPhone(Request $request)
    {
        return $this->service->bindAppLoginPhone($request);

    }

//    public function processingAlarmCreate(Request $request)
//    {
//        return $this->service->processingAlarmCreate($request);
//
//    }

    public function updateVehicleBattery(Request $request)
    {
        return $this->service->updateVehicleBattery($request);

    }

    public function setKey(Request $request){
        return $this->service->setKey($request);

    }

    public function delKey(Request $request){
        return $this->service->delKey($request);

    }
    public function queryKey(Request $request){
        return $this->service->queryKey($request);

    }
}
