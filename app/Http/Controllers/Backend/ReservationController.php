<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Service\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new ReservationService();
    }

    public function reservationRecord(Request $request)
    {
        return  $this->service->reservationRecord($request);
    }

    public function complaintRecord(Request $request)
    {
        return  $this->service->complaintRecord($request);

    }

    public function complaintUpdate(Request $request)
    {
        return  $this->service->complaintUpdate($request);
    }

    public function refundRecord(Request $request)
    {
        return  $this->service->refundRecord($request);

    }

    public function drivingRecord(Request $request)
    {
        return  $this->service->drivingRecord($request);
    }


}
