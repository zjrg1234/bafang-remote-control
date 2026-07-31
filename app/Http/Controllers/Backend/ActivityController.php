<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Service\ActivityService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    //
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new ActivityService();
    }

    public function List(Request $request)
    {
        return $this->service->List($request);
    }

    public function Create(Request $request)
    {
        return $this->service->Create($request);
    }

    public function Update(Request $request)
    {
        return $this->service->Update($request);
    }

    public function Delete(Request $request)
    {
        return $this->service->Delete($request);
    }

    public function ChangeStatus(Request $request)
    {
        return $this->service->ChangeStatus($request);
    }
}
