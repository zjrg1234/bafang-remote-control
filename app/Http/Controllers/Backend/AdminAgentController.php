<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Service\AgentService;
use Illuminate\Http\Request;

class AdminAgentController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new AgentService();
    }
    public function list(Request $request)
    {
        return  $this->service->list($request);
    }

    public function detail(Request $request)
    {
        return  $this->service->detail($request);
    }

    public function create(Request $request)
    {
        return  $this->service->create($request);
    }

    public function update(Request $request)
    {
        return  $this->service->update($request);
    }

    public function vehicleList(Request $request)
    {
        return  $this->service->vehicleList($request);
    }
    public function vehicleDetail(Request $request)
    {
        return  $this->service->vehicleDetail($request);
    }
    public function walletLog(Request $request)
    {
        return  $this->service->walletLog($request);

    }
    public function changePassword(Request $request)
    {
        return  $this->service->changePassword($request);

    }

    public function Frozen(Request $request)
    {
        return  $this->service->Frozen($request);

    }
    public function takeDown(Request $request)
    {
        return  $this->service->takeDown($request);

    }
    public function updateYesterdayTurnover(Request $request)
    {
        return  $this->service->updateYesterdayTurnover($request);

    }

    public function venueList(Request $request)
    {
        return  $this->service->venueList($request);

    }
    public function delete(Request $request)
    {
        return  $this->service->delete($request);

    }

    public function agentDelete(Request $request)
    {
        return  $this->service->agentDelete($request);

    }

    public function venueTakeDown(Request $request)
    {
        return  $this->service->venueTakeDown($request);

    }

    public function venueDelete(Request $request)
    {
        return  $this->service->venueDelete($request);

    }

    public function venueChangeSort(Request $request)
    {
        return  $this->service->venueChangeSort($request);

    }

    public function venueVehicleList(Request $request)
    {
       return $this->service->venueVehicleList($request);
    }

    public function changeBalance(Request $request)
    {
        return $this->service->changeBalance($request);

    }

}
