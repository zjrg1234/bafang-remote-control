<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\NoticeService;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new NoticeService();
    }

    public function list(Request $request)
    {
        return $this->service->list($request);
    }

    public function create(Request $request)
    {
        return $this->service->create($request);
    }

    public function update(Request $request)
    {
        return $this->service->update($request);
    }

    public function delete(Request $request)
    {
        return $this->service->delete($request);
    }

    public function notice(Request $request)
    {
        return $this->service->notice($request);
    }
}
