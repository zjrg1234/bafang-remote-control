<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Service\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new UserService();
    }

    public function agentMine(Request $request)
    {
        return $this->service->agentMine($request);
    }


}
