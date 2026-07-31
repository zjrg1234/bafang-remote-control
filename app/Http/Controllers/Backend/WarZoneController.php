<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Service\WarZoneService;
use App\Models\AgentWalletLog;
use App\Models\CuserAgent;
use App\Models\CuserEnergyLog;
use App\Models\CuserWalletLog;
use App\Models\ReponseData;
use App\Models\WarZone;
use Illuminate\Http\Request;

class WarZoneController extends Controller
{
    //
    protected $service;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->service = new WarZoneService();
    }

    public function create(Request $request)
    {
        $data = [
             'name' => $request['name'],
        ];

        if(!$data['name']){
            return ReponseData::reponseFormat(400,'专区名称必填');
        }
        $exists = WarZone::query()->where('name', $data['name'])->first();
        if($exists){
            return ReponseData::reponseFormat(400,'该专区已存在');
        }
        WarZone::create($data);

        return ReponseData::reponseFormat(200,'创建成功');
    }

    public function list(Request $request)
    {

        $query =  CuserAgent::select('id','agent_name')->where('level',1)->get();


        return ReponseData::reponseFormatList(200,'获取成功',$query);
    }

    public function typeList(Request $request)
    {
        $type = $request->get('type') ?? 1;
        if($type == 1){
            $data = CuserWalletLog::$typeNames;
        }else{
            $data = CuserEnergyLog::$typeNames;
        }
        return ReponseData::reponseFormatList(200,'成功',$data);
    }

    public function agentTypeList(Request $request)
    {
        $data = AgentWalletLog::$typeNames;
        return ReponseData::reponseFormatList(200,'成功',$data);
    }

    public function vehicleList(Request $request)
    {
        return $this->service->vehicleList($request);
    }

}
