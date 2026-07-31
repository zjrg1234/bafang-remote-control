<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new VehicleService();
    }
    public function vehicleList(Request $request)
    {
        return  $this->service->vehicleList($request);
    }

    public function bindingVenue(Request $request)
    {
        return  $this->service->bindingVenue($request);
    }
    public function deleteVehicle(Request $request)
    {
        return  $this->service->deleteVehicle($request);
    }

    public function downVehicle(Request $request){
        return  $this->service->downVehicle($request);
    }

    public function unBindingVenue(Request $request)
    {
        return $this->service->unbindVehicle($request);
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

    public function processingAlarm(Request $request)
    {
        return $this->service->processingAlarm($request);

    }

    public function processingAlarmList(Request $request)
    {
        return $this->service->processingAlarmList($request);

    }

    public function processingAlarmDelete(Request $request)
    {
        return $this->service->processingAlarmDelete($request);

    }

    public function vehicleDetailReset(Request $request)
    {
        return $this->service->vehicleDetailReset($request);

    }

    public function processingAlarmCreate(Request $request)
    {
        return $this->service->processingAlarmCreate($request);

    }

    public function updateVehicleBattery(Request $request)
    {
        return $this->service->updateVehicleBattery($request);

    }
}
