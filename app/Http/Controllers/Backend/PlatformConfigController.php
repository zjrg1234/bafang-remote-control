<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Service\PlatformConfigService;
use Illuminate\Http\Request;

class PlatformConfigController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new PlatformConfigService();
    }

    public function commonProblemList(Request $request)
    {
        return $this->service->commonProblemList($request);
    }

    public function commonProblemCreate(Request $request)
    {
        return $this->service->commonProblemCreate($request);
    }
    public function commonProblemUpdate(Request $request)
    {
        return $this->service->commonProblemUpdate($request);
    }
    public function commonProblemDelete(Request $request)
    {
        return $this->service->commonProblemDelete($request);

    }

    public function protocolManageList(Request $request)
    {
        return $this->service->protocolManageList($request);
    }
    public function protocolManageUpdate(Request $request)
    {
        return $this->service->protocolManageUpdate($request);
    }

    public function feedBackList(Request $request)
    {
        return $this->service->feedBackList($request);
    }
    public function feedBackUpdate(Request $request)
    {
        return $this->service->feedBackUpdate($request);
    }

    public function advertisingList(Request $request)
    {
        return $this->service->advertisingList($request);

    }

    public function advertisingCreate(Request $request)
    {
        return $this->service->advertisingCreate($request);

    }

    public function advertisingUpdate(Request $request)
    {
        return $this->service->advertisingUpdate($request);

    }

    public function advertisingDelete(Request $request)
    {
        return $this->service->advertisingDelete($request);
    }

    public function versionList(Request $request)
    {
        return $this->service->versionList($request);
    }

    public function versionCreate(Request $request)
    {
        return $this->service->versionCreate($request);
    }

    public function versionUpdate(Request $request)
    {
        return $this->service->versionUpdate($request);
    }

    public function versionDelete(Request $request)
    {
        return $this->service->versionDelete($request);

    }

    public function popupList(Request $request)
    {
        return $this->service->popupList($request);

    }

    public function popupCreate(Request $request)
    {
        return $this->service->popupCreate($request);
    }
    public function popupUpdate(Request $request)
    {
        return $this->service->popupUpdate($request);
    }


    public function popupDelete(Request $request)
    {
        return $this->service->popupDelete($request);
    }

    public function parameterList(Request $request)
    {
        return $this->service->parameterList($request);

    }

    public function parameterUpdate(Request $request)
    {
        return $this->service->parameterUpdate($request);

    }


    public function vehicleImageList(Request $request)
    {
        return $this->service->vehicleImageList($request);

    }

    public function vehicleImageCreate(Request $request)
    {
        return $this->service->vehicleImageCreate($request);
    }
    public function vehicleImageUpdate(Request $request)
    {
        return $this->service->vehicleImageUpdate($request);
    }


    public function vehicleImageDelete(Request $request)
    {
        return $this->service->vehicleImageDelete($request);
    }

    public function vehicleImageChangeStatus(Request $request)
    {
        return $this->service->vehicleImageChangeStatus($request);
    }

    public function vehicleImageTypeList(Request $request)
    {
        return $this->service->vehicleImageTypeList($request);

    }
}

