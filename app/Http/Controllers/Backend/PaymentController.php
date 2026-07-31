<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Service\PaymentService;

class PaymentController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new PaymentService();
    }

    public function paymentList(Request $request)
    {
        return $this->service->paymentList($request);
    }

    public function withdrawList(Request $request)
    {
        return $this->service->withdrawList($request);
    }
    public function withdrawAudit(Request $request)
    {
        return $this->service->withdrawAudit($request);

    }

    public function refundList(Request $request)
    {
        return $this->service->refundList($request);
    }

    public function specialAccountList(Request $request)
    {
        return $this->service->specialAccountList($request);

    }

    public function specialDepositList(Request $request)
    {
        return $this->service->specialDepositList($request);
    }

    public function Index(Request $request)
    {
        return $this->service->Index($request);
    }
}
