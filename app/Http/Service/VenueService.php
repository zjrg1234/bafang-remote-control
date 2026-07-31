<?php

namespace App\Http\Service;

use App\Models\AgentVenue;
use App\Models\CuserAgent;
use App\Models\DrivingRecord;
use App\Models\ReponseData;
use App\Models\Vehicle;

class VenueService{

    protected $setvice;
    public function __construct()
    {
        $this->setvice = new LoginService();
    }
    public function venueList($request){
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'agent_id' => $request['agent_id'] ?? null,
//            'type' => $request['type'] ?? null,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2001,'代理id必传!');
        }
        $exists = CuserAgent::where('id', $data['agent_id'])->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该代理!');
        }
//        if(!$data['type']){
//            return ReponseData::reponseFormat(2003,'分配状态必传!');
//        }
//        if($data['type'] == 1){
            $list = AgentVenue::select('id','agent_id','venue_name','venue_image','venue_introduction','labels','label_id','start_time','end_time','venue_config','support_status')->where(['agent_id'=>$data['agent_id']])->get();
//        }else{
//            $list = AgentVenue::select('id','agent_id','venue_name','venue_image','venue_introduction','labels','start_time','end_time','venue_config','support_status')->where(['agent_id'=>$data['agent_id'],'support_status' => 2])->get();
//        }
        $respList = [
            'on_business'=>[],
            'off_business'=>[],
        ];

        foreach($list as $value){
            $online = Vehicle::where(['agent_id'=>$data['agent_id'],'venue_id'=>$value['id']])->whereIn('vehicle_state',[1,2])->count(); //在线车辆
            $drive = Vehicle::where(['agent_id'=>$data['agent_id'],'venue_id'=>$value['id'],'vehicle_state'=>2])->count(); //驾驶中车辆
            $car_number = Vehicle::where(['agent_id'=>$data['agent_id'],'venue_id'=>$value['id']])->count(); //车辆总数

            $value['venue_image'] = explode(',',$value['venue_image']);
            $people_number = 0;//表未建立 暂定
            $value['online'] = $online;
            $value['drive'] = $drive;
            $value['people_number'] = $people_number;
            $value['start_time'] = date('H:i',$value['start_time']);
            $value['end_time'] = date('H:i',$value['end_time']);
            $value['car_number'] = $car_number;
            $venue_config = json_decode($value['venue_config'],true);
            if(isset($venue_config['one_billing'])){
                $value['one_billing'] = $venue_config['one_billing'];
            }
            if(isset($venue_config['time_billing'])){
                $value['time_billing'] = $venue_config['time_billing'];
            }
            unset($value['venue_config']);
            if($value['support_status'] == 1){
                $respList['on_business'][] = $value;
            }else{
                $respList['off_business'][] = $value;
            }
        }
        return ReponseData::reponseFormatList(200,'获取成功',$respList);
    }

    public function createVenue($request){
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'agent_id' => $request['agent_id'],
            'venue_image' => $request['venue_image'] ?? null,
            'venue_name' => $request['venue_name'] ?? null,
            'start_time' => strtotime($request['start_time']) ?? null,
            'end_time' => strtotime($request['end_time']) ?? null,
            'venue_introduction' => $request['venue_introduction'] ?? null,
            'labels' => $request['labels'],
            'one_billing' => $request['one_billing'] ?? null,
            'time_billing' => $request['time_billing'] ?? null,
            'label_id' => $request['labels_id'] ?? null,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2001,'代理id必传!');
        }
        if(!$data['label_id']){
            return ReponseData::reponseFormat(2001,'场地标签id必传!');
        }

        $agent = CuserAgent::where('id', $data['agent_id'])->first();

        if(!$agent){
            return ReponseData::reponseFormat(2004,'未查询到该代理!');
        }

        if(!$data['venue_image']){
            return ReponseData::reponseFormat(2001,'主图必传!');
        }

        if(!$data['venue_name']){
            return ReponseData::reponseFormat(2001,'场地名称必填!');
        }

        if(!$data['start_time'] || !$data['end_time']){
            return ReponseData::reponseFormat(2001,'营业时间必填!');
        }

        if(!$data['labels']){
            return ReponseData::reponseFormat(2001,'场地名称必填!');
        }

        if($data['one_billing']){
            $venueConfig['one_billing'] = $data['one_billing'];
        }

        if($data['time_billing']){
            $venueConfig['time_billing'] = $data['time_billing'];
        }


        $count = AgentVenue::where('agent_id',$data['agent_id'])->count();
        if($count >=  $agent['create_site_quantity']){
            return  ReponseData::reponseFormat(2000,'场地创建数量达到上限');
        }

        $insertData = [
            'agent_id' => $request['agent_id'],
            'venue_image' => $request['venue_image'],
            'venue_name' => $request['venue_name'],
            'start_time' => strtotime($request['start_time']),
            'end_time' => strtotime($request['end_time']),
            'venue_introduction' => $data['venue_introduction'] ?? '',
            'labels' => $request['labels'],
            'venue_config' => json_encode($venueConfig,true),
            'support_status' => 2,
            'label_id' => $data['label_id'],
        ];

        AgentVenue::create($insertData);

        return ReponseData::reponseFormat(200,'新建成功!');

    }
    public function venueDetail($request){
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'agent_id' => $request['agent_id'] ?? null,
            'venue_id' => $request['venue_id'] ?? null,
        ];

        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2001,'代理id必传!');
        }

        if(!$data['venue_id']){
            return ReponseData::reponseFormat(2001,'场地id必传!');
        }
        $exists = CuserAgent::where('id', $data['agent_id'])->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该代理!');
        }

        $list = AgentVenue::select('id','agent_id','venue_name','venue_image','venue_introduction','labels','start_time','end_time','venue_config')->where(['id'=>$data['venue_id']])->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该场地');
        }
        $online = Vehicle::where(['agent_id'=>$data['agent_id'],'venue_id'=>$data['venue_id']])->whereIn('vehicle_state',[1,2])->count(); //在线车辆
        $drive = Vehicle::where(['agent_id'=>$data['agent_id'],'venue_id'=>$data['venue_id'],'vehicle_state'=>2])->count(); //驾驶中车辆
        $people_number = DrivingRecord::where('venue_id', $data['venue_id'])->whereIn('reservation_status',[1,2])->count();//表未建立 暂定
        $list['online'] = $online;
        $list['drive'] = $drive;
        $list['people_number'] = $people_number;
        $list['start_time'] = date('H:i',$list['start_time']);
        $list['end_time'] = date('H:i',$list['end_time']);
        $list['venue_image'] = explode(',',$list['venue_image']);
        $venue_config = json_decode($list['venue_config'],true);
        $list['venue_config'] = $venue_config;
        if(isset($venue_config['one_billing'])){
            $list['one_billing'] = $venue_config['one_billing'];
        }

        if(isset($venue_config['time_billing'])){
            $list['time_billing'] = $venue_config['time_billing'];
        }
        $vehicle = Vehicle::select('id','vehicle_name','vehicle_introduction','top_speed','vehicle_image','vehicle_state','is_password','vehicle_battery','vehicle_sorting')->where(['agent_id'=>$data['agent_id'],'venue_id'=>$list['id']])->orderBy('vehicle_sorting', 'asc')->get(); //车辆列表
        $list['vehicle'] = $vehicle;

        return ReponseData::reponseFormatList(200,'成功',$list);
    }

    public function updateVenue($request){
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['venue_id'];
        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        $list = AgentVenue::select('id','agent_id','venue_name','venue_image','venue_introduction','labels','start_time','end_time','venue_config')->where('id',$id)->first();
        $updateData = [
            'venue_image' => $request['venue_image'] ?? $list['venue_image'],
            'venue_name' => $request['venue_name'] ?? $list['venue_name'],
            'start_time' => strtotime($request['start_time']) ?? $list['start_time'],
            'end_time' => strtotime($request['end_time']) ?? $list['end_time'],
            'venue_introduction' => $request['venue_introduction'] ?? $list['venue_introduction'],
            'labels' => $request['labels'] ?? $list['labels'],
            'label_id' => $request['labels_id'] ?? $list['label_id'],
            'one_billing' => $request['one_billing']  ?? null,
            'time_billing' => $request['time_billing'] ?? null,
        ];

        if($updateData['one_billing']){
            $venueConfig['one_billing'] = $updateData['one_billing'];
        }

        if($updateData['time_billing']){
            $venueConfig['time_billing'] = $updateData['time_billing'];
        }

        $updateData['venue_config'] = json_encode($venueConfig);
        $list->update($updateData);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function venueBusiness($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['venue_id'] ?? null;
        $agent_id = $request['agent_id'] ?? null;
        $type = $request['type'] ?? null;
        if(!$agent_id){
            return ReponseData::reponseFormat(2001,'代理id必传!');
        }
        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }
        if(!$type){
            return ReponseData::reponseFormat(2001,'type必传!');
        }
        $exists = CuserAgent::where('id', $agent_id)->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该代理!');
        }
        $venue = AgentVenue::where('id', $id)->first();
        if(!$venue){
            return ReponseData::reponseFormat(2004,'未找到该记录!');
        }

        $venue->support_status = $type;
        $venue->save();
        if($type == 1){
            $message = '开始营业成功';
        }else{
            $message = '关闭营业成功';

        }
        return ReponseData::reponseFormat(200,$message);
    }

    public function venueDelete($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['venue_id'] ?? null;

        $agent_id = $request['agent_id'] ?? null;
        if(!$agent_id){
            return ReponseData::reponseFormat(2001,'代理id必传!');
        }
        if(!$id){
            return ReponseData::reponseFormat(2001,'id必传!');
        }

        $exists = CuserAgent::where('id', $agent_id)->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该代理!');
        }
        $venue = AgentVenue::where('id', $id)->first();
        if(!$venue){
            return ReponseData::reponseFormat(2004,'未找到该记录!');
        }
        Vehicle::where('agent_id', $agent_id)->where('venue_id', $id)->update(['venue_id'=>0]);

        $venue->delete();

        return ReponseData::reponseFormat(200,'删除成功');
    }
}
