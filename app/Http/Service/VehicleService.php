<?php

namespace App\Http\Service;


use App\Models\AgentVenue;
use App\Models\AgentWallet;
use App\Models\AgentWalletLog;
use App\Models\AlarmVehcle;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Models\DrivingRecord;
use App\Models\ReponseData;
use App\Models\Vehicle;
use App\Models\VehicleConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VehicleService
{
    protected $setvice;
    public function __construct()
    {
        $this->setvice = new LoginService();
    }
    public function vehicleList($request)
    {
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
//            return ReponseData::reponseFormat(2003,'状态必传!');
//        }
//        if($data['type'] != 1){
//            $list = Vehicle::select('id','vehicle_name','vehicle_image','vehicle_introduction','vehicle_battery','top_speed','status')->where(['agent_id'=>$data['agent_id'],'venue_id'=>0])->get();
//        }else{
            $list = Vehicle::select('id','venue_id','venue_name','vehicle_name','vehicle_type','vehicle_image','vehicle_introduction','vehicle_battery','top_speed','vehicle_state','receiver_id','vehicle_sorting','status')
                ->where('agent_id',$data['agent_id'])
                ->orderby('vehicle_sorting','asc')
                ->get();
//        }
        $venueIds = Vehicle::select('id','venue_id','venue_name','vehicle_name','vehicle_type','vehicle_image','vehicle_introduction','vehicle_battery','top_speed','vehicle_state','receiver_id','vehicle_sorting','status')
            ->where('agent_id',$data['agent_id'])
            ->pluck('venue_id');
        $venueNameData = AgentVenue::query()
            ->whereIn('id', $venueIds)
            ->pluck('venue_name', 'id')
            ->toArray();
        $respList = [
            'on_allocate'=>[],
            'off_allocate'=>[],
        ];
        foreach($list as $value){
            if($value['venue_id'] != 0){
                $value['venue_name'] = $venueNameData[$value['venue_id']] ?? '';
                $respList['on_allocate'][] = $value;
            }else{
                $value['venue_name'] = $venueNameData[$value['venue_id']] ?? '';
                $respList['off_allocate'][] = $value;
            }
        }
        return ReponseData::reponseFormatList(200,'获取成功',$respList);

    }

    public function bindingVenue($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'vehicle_id' => $request['id'],
            'venue_id' => $request['venue_id'],
            'type' => $request['type'] ?? null,
        ];
        if(!$data['type']){
            return ReponseData::reponseFormat(2004,'type必传!');

        }
        $venue = AgentVenue::where('id', $data['venue_id'])->first();
//        if(!$venue){
//            return ReponseData::reponseFormat(2004,'未查询到该场地!');
//        }
        $vehicle = Vehicle::where('id', $data['vehicle_id'])->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2002,'未查询到该车辆!');
        }
        if($data['type'] == 1){
            if($venue){
                $vehicle['venue_id'] = $data['venue_id'];
                $vehicle['venue_name'] = $venue['venue_name'];
            }else{
                return ReponseData::reponseFormat(2004,'未查询到该场地!');
            }
            $vehicle->save();
            $message = '车辆绑定场地成功!';
        }else{
            $vehicle['venue_id'] = 0;
            $vehicle['venue_name'] = '';
            $vehicle->save();
            $message = '车辆解绑场地成功!';
        }

        return ReponseData::reponseFormat(200,$message);
    }

    public function deleteVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $vehicleId = $request['id'] ?? null;
        $agent_id =  $request['agent_id'] ?? null;
        if(!$agent_id){
            return ReponseData::reponseFormat(2000,'代理id必传');
        }
        if(!$vehicleId){
            return ReponseData::reponseFormat(2004,'车辆id必传!');
        }
        $vehicle = Vehicle::where('id', $vehicleId)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2002,'未查询到该车辆或已被删除!');
        }
        $vehicle->delete();
        VehicleConfig::where('vehicle_id', $vehicleId)->delete();


        return ReponseData::reponseFormat(200,'车辆删除成功!');

    }

    public function downVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $vehicleId = $request['id'] ?? null;
        $type = $request['type'] ?? null;
        if(!$vehicleId){
            return ReponseData::reponseFormat(2004,'车辆id必传!');
        }
        if(!$type){
            return ReponseData::reponseFormat(2004,'type必传!');
        }
        $vehicle = Vehicle::where('id', $vehicleId)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2002,'未查询到该车辆或已被删除!');
        }
        if($type == 1){
            $alarm = AlarmVehcle::where('vehicle_id', $vehicleId)->where('status',0)->first();
            if($alarm){
                return ReponseData::reponseFormat(2000,'车辆有告警报修未处理,请先处理哦！');
            }
            $vehicle->update(['status'=>1]);
            $message = '车辆上架成功!';
        }else{
            $vehicle->update(['status'=>0]);
            $message = '车辆下架成功!';


        }

        return ReponseData::reponseFormat(200,$message);

    }

    public function unbindVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'vehicle_id' => $request['id'] ?? null,
//            'venue_id' => $request['venue_id'],
        ];
//        $venue = AgentVenue::where('id', $data['venue_id'])->first();
//        if(!$venue){
//            return ReponseData::reponseFormat(2004,'未查询到该场地!');
//        }
        if(!$data['vehicle_id']){
            return ReponseData::reponseFormat(2002,'id必传!');
        }
        $vehicle = Vehicle::where('id', $data['vehicle_id'])->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2002,'未查询到该车辆!');
        }
        $vehicle['venue_id'] = 0;
        $vehicle->save();

        return ReponseData::reponseFormat(200,'车辆解绑成功!');

    }

//    public function downVenue($request)
//    {
//        $request = $this->setvice->decrypt($request['data']);
//        $data = [
//            'vehicle_id' => $request['vehicle_id'],
//        ];
//        $vehicle = Vehicle::where('id', $data['vehicle_id'])->first();
//        if(!$vehicle){
//            return ReponseData::reponseFormat(2002,'未查询到该车辆!');
//        }
//        $vehicle['status'] = 0;
//        $vehicle->save();
//        return ReponseData::reponseFormat(200,'车辆下架成功!');
//    }

    public function addVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'vehicle_image' => $request['vehicle_image'] ?? null,
            'battery' => $request['battery'] ?? null,
            'vehicle_name' => $request['vehicle_name'] ?? null,
            'vehicle_introduction' => $request['vehicle_introduction'] ?? '',
            'top_speed' => $request['top_speed'] ?? '',
            'front_camera' => $request['front_camera'] ?? null,
            'rear_camera' =>  $request['rear_camera'] ?? '',
            'transmitter_id' => $request['transmitter_id'] ?? '',
            'receiver_id' => $request['receiver_id'] ?? null,
            'vehicle_type' => $request['vehicle_type'] ?? null,
            'vehicle_sorting' => $request['vehicle_sorting'] ?? '1',
            'agent_id' => $request['agent_id'] ?? null,
            'forward_type' => $request['type'] ?? 1,
            'camera_type' => $request['camera_type'] ?? 1,
        ];
        if(!$data['agent_id']){
            return ReponseData::reponseFormat(2000,'代理id必传!');
        }
        if(!$data['vehicle_image']){
            return ReponseData::reponseFormat(2000,'车辆图片必填!');
        }
        if(!$data['battery']){
            return ReponseData::reponseFormat(2000,'车辆电池必填!');
        }
        if(!$data['vehicle_name']){
            return ReponseData::reponseFormat(2000,'车辆名称必填!');
        }
        if(!$data['front_camera']){
            return ReponseData::reponseFormat(2000,'前摄像头必填!');
        }
        if(!$data['vehicle_type']){
            return ReponseData::reponseFormat(2000,'车辆类型必填!');
        }
//        if(!$data['forward_type']){
//            return ReponseData::reponseFormat(2000,'一代机二代机必须填');
//
//        }
        $data['vehicle_battery'] = '5%';
        $vehicleConfig = [
            'direction_dynamics' => json_encode([
                'mini_value'=>1,
                'max_value'=>100,
                'current_value'=>60,
            ]), //方向力度
//            'turn_left' => 1000,
//            'turn_right' => 1000,
            'accelerator_dynamics' => json_encode([
                'mini_value'=>1,
                'max_value'=>100,
                'current_value'=>50,
            ]), //油门力度
            'direction_center' => json_encode([
                'mini_value'=>500,
                'max_value'=>1500,
                'current_value'=>1000,
            ]), //方向中位
            'accelerator_center' => json_encode([
                'mini_value'=>500,
                'max_value'=>1500,
                'current_value'=>1000,
            ]), //油门中位
            'video_definition' => '2,3,4',
            'rear_camera_type' => 0,
            'operation_mode' => 0,
        ];
        $channelConfig = [
            'ch1'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1500,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>500,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch2'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1500,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>500,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch3'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1300,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>700,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch4'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1300,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>700,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch5'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1300,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>700,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch6'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1300,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>700,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch7'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1300,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>700,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
            'ch8'=>[
                'open_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1300,
                ],
                'close_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>700,
                ],
                'center_value'=>[
                    'mini_value'=>1,
                    'max_value'=>2000,
                    'current_value'=>1000,
                ],
            ],
        ];

        if($data['vehicle_type'] >=20 && $data['vehicle_type'] <=29 ){
            $channelConfig = [
                'ch1'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch2'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch3'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch4'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch5'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch6'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch7'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch8'=>[
                    'open_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
            ];
        }
        $vehicleConfig['vehicle_config_detail'] = json_encode($channelConfig);
        $exists = Vehicle::where('receiver_id', $data['receiver_id'])->first();
        if($exists){
            return ReponseData::reponseFormat(2000,'接收机重复!');
        }
        $data['app_transmitter_id'] = mt_rand(40000000,49999999);

        $vehicle = Vehicle::create($data);
        $vehicleConfig['vehicle_id'] = $vehicle['id'];
        $vehicleConfig['camera_type'] = $data['camera_type'];
        VehicleConfig::create($vehicleConfig);

        return ReponseData::reponseFormat(200,'车辆新增成功');
    }

    public function vehicleDetail($request){
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $vehicle = Vehicle::select('*')->where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2001,'未找到该车辆!');
        }
        $vehicleConfig = VehicleConfig::where('vehicle_id', $id)->first();
        $vehicleConfig['id'] = $vehicleConfig['vehicle_id'];
        if(!$vehicleConfig){
            return ReponseData::reponseFormat(2001,'未找到该车辆配置!');
        }

        $vehicleConfig['direction_dynamics'] = json_decode($vehicleConfig['direction_dynamics']);
        $vehicleConfig['accelerator_dynamics'] = json_decode($vehicleConfig['accelerator_dynamics']);
        $vehicleConfig['direction_center'] = json_decode($vehicleConfig['direction_center']);
        $vehicleConfig['accelerator_center'] = json_decode($vehicleConfig['accelerator_center']);
        $vehicleConfig['vehicle_name'] = $vehicle['vehicle_name'];
        $vehicleConfig['vehicle_type'] = $vehicle['vehicle_type'];
        $vehicleConfig['vehicle_image'] = $vehicle['vehicle_image'];
        $vehicleConfig['vehicle_battery'] = $vehicle['vehicle_battery'];
        $vehicleConfig['battery'] = intval($vehicle['battery']);
        $vehicleConfig['front_camera'] = $vehicle['front_camera'];
        $vehicleConfig['rear_camera'] = $vehicle['rear_camera'];
        $vehicleConfig['transmitter_id'] = $vehicle['transmitter_id'];
        $vehicleConfig['receiver_id'] = $vehicle['receiver_id'];
        $vehicleConfig['vehicle_sorting'] = $vehicle['vehicle_sorting'];
        $vehicleConfig['top_speed'] = $vehicle['top_speed'];
        $vehicleConfig['vehicle_introduction'] = $vehicle['vehicle_introduction'];
        $vehicleConfig['vehicle_config_detail'] = json_decode($vehicleConfig['vehicle_config_detail']);
        $vehicleConfig['password'] = $vehicle['password'];
        $vehicleConfig['forward_type'] = $vehicle['forward_type'];
        $vehicleConfig['app_transmitter_id'] = $vehicle['app_transmitter_id'];
        $vehicleConfig['reverse_left_right'] = $vehicle['reverse_left_right'];
        $vehicleConfig['reverse_up_down'] = $vehicle['reverse_up_down'];
        $vehicleConfig['reverse_rotation'] = $vehicle['reverse_rotation'];
        $vehicleConfig['change_ui_control'] = $vehicle['change_ui_control'];

        $vehicleConfig['content_url'] = env('CONTENT_URL','xhzzf.huazyk.cn') ;
        $vehicleConfig['content_url_port'] = env('CONTENT_URL_PORT','8899') ;
        $vehicleConfig['web_camera_host'] = env('WEB_CAMERA_HOST','') ;
        $vehicleConfig['web_camera_port'] = env('WEB_CAMERA_PORT','') ;
        $vehicleConfig['web_camera_user_name'] = env('WEB_CAMERA_NAME','') ;
        $vehicleConfig['web_camera_user_password'] = env('WEB_CAMERA_PASSWORD','') ;
        return ReponseData::reponseFormatList(200,'成功!',$vehicleConfig);
    }

    public function vehicleDetailSave($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $password = $request['password'] ?? null;

        $id = $request['id'];
        $vehicleConfigDetail = $request['vehicle_config_detail'];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }

        $vehicle = Vehicle::where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'车辆未找到');
        }
        $reverse_left_right = $request['reverse_left_right'] ?? $vehicle['reverse_left_right'];
        $reverse_up_down = $request['reverse_up_down'] ?? $vehicle['reverse_up_down'];
        $reverse_rotation = $request['reverse_rotation'] ?? $vehicle['reverse_rotation'];
        $change_ui_control = $request['change_ui_control'] ?? $vehicle['change_ui_control'];
        $vehicleConfig = VehicleConfig::where('vehicle_id', $id)->first();

        if(!$vehicleConfig){
            return ReponseData::reponseFormat(2001,'未找到该车辆配置!');
        }
        foreach($vehicleConfigDetail as  &$v){
            $v['open_value']['mini_value'] = intval($v['open_value']['mini_value']);
            $v['open_value']['max_value'] = intval($v['open_value']['max_value']);
            $v['open_value']['current_value'] = intval($v['open_value']['current_value']);

            $v['close_value']['mini_value'] = intval($v['close_value']['mini_value']);
            $v['close_value']['max_value'] = intval($v['close_value']['max_value']);
            $v['close_value']['current_value'] = intval($v['close_value']['current_value']);

            $v['center_value']['mini_value'] = intval($v['center_value']['mini_value']);
            $v['center_value']['max_value'] = intval($v['center_value']['max_value']);
            $v['center_value']['current_value'] = intval($v['center_value']['current_value']);
        }

        if($vehicle['vehicle_type'] >= 10 && $vehicle['vehicle_type'] < 20 ){
            $get_vehicle_config_detail = json_decode($vehicleConfig['vehicle_config_detail'],true);
            $vehicleConfigDetail['ch1'] = $get_vehicle_config_detail['ch1'];
            $vehicleConfigDetail['ch2'] = $get_vehicle_config_detail['ch2'];
        }
        $data = [
            'direction_dynamics' => json_encode($request['direction_dynamics']) ?? $vehicleConfig['direction_dynamics'],
            'accelerator_dynamics' => json_encode($request['accelerator_dynamics']) ?? $vehicleConfig['accelerator_dynamics'],
            'direction_center' => json_encode($request['direction_center']) ?? $vehicleConfig['direction_center'],
            'accelerator_center' => json_encode($request['accelerator_center']) ?? $vehicleConfig['accelerator_center'],
            'video_definition' => $request['video_definition'] ?? $vehicleConfig['video_definition'],
            'rear_camera_type' => $request['rear_camera_type'] ?? $vehicleConfig['rear_camera_type'],
            'operation_mode' => $request['operation_mode'] ?? $vehicleConfig['operation_mode'],
            'mixed_control' => $request['mixed_control'] ?? $vehicleConfig['mixed_control'],
            'vehicle_config_detail' => json_encode($vehicleConfigDetail),
            'default_camera_clarity' => $request['default_camera_clarity'] ?? $vehicleConfig['default_camera_clarity'],

        ];
        $vehicleConfig->update($data);
        if($password){
            Vehicle::where('id', $id)->update(['password' => $password,
                'is_password'=>1,
                'reverse_left_right'=>$reverse_left_right,
                'reverse_up_down'=>$reverse_up_down,
                'reverse_rotation'=>$reverse_rotation,
                'change_ui_control'=>$change_ui_control,]);
        } elseif(isset($request['password'])) {
            Vehicle::where('id', $id)->update(['password' => '',
                'is_password'=>0,
                'reverse_left_right'=>$reverse_left_right,
                'reverse_up_down'=>$reverse_up_down,
                'reverse_rotation'=>$reverse_rotation,
                'change_ui_control'=>$change_ui_control,]);
        }
        else
        {
            Vehicle::where('id', $id)->update([
                'reverse_left_right'=>$reverse_left_right,
                'reverse_up_down'=>$reverse_up_down,
                'reverse_rotation'=>$reverse_rotation,
                'change_ui_control'=>$change_ui_control,]);
        }
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function updateVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['id'];

        $data = [
            'vehicle_image' => $request['vehicle_image'] ?? null,
            'battery' => $request['battery'] ?? null,
            'vehicle_name' => $request['vehicle_name'] ?? null,
            'vehicle_introduction' => $request['vehicle_introduction'] ?? '',
            'top_speed' => $request['top_speed'] ?? '',
            'front_camera' => $request['front_camera'] ?? null,
            'rear_camera' =>  $request['rear_camera'] ?? '',
            'transmitter_id' => $request['transmitter_id'] ?? '',
            'receiver_id' => $request['receiver_id'] ?? null,
            'vehicle_type' => $request['vehicle_type'] ?? null,
            'vehicle_sorting' => $request['vehicle_sorting'] ?? '0',
            'forward_type' => $request['type'] ?? 1,
            'camera_type' => $request['camera_type'] ?? 1
        ];

        $vehicle = Vehicle::where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2007,'未找到该车辆!');

        }
        if(!$data['vehicle_image']){
            return ReponseData::reponseFormat(2000,'车辆图片必填!');
        }
        if(!$data['battery']){
            return ReponseData::reponseFormat(2000,'车辆电池必填!');
        }
        if(!$data['vehicle_name']){
            return ReponseData::reponseFormat(2000,'车辆名称必填!');
        }
        if(!$data['front_camera']){
            return ReponseData::reponseFormat(2000,'前摄像头必填!');
        }
        if(!$data['vehicle_type']){
            return ReponseData::reponseFormat(2000,'车辆类型必填!');
        }
        if($vehicle['vehicle_type'] != $data['vehicle_type']){
            $vehicleConfig = [
                'direction_dynamics' => json_encode([
                    'mini_value'=>1,
                    'max_value'=>100,
                    'current_value'=>60,
                ]), //方向力度
//            'turn_left' => 1000,
//            'turn_right' => 1000,
                'accelerator_dynamics' => json_encode([
                    'mini_value'=>1,
                    'max_value'=>100,
                    'current_value'=>50,
                ]), //油门力度
                'direction_center' => json_encode([
                    'mini_value'=>500,
                    'max_value'=>1500,
                    'current_value'=>1000,
                ]), //方向中位
                'accelerator_center' => json_encode([
                    'mini_value'=>500,
                    'max_value'=>1500,
                    'current_value'=>1000,
                ]), //油门中位
                'video_definition' => '2,3,4',
                'rear_camera_type' => 0,
                'operation_mode' => 0,
            ];
            $channelConfig = [
                'ch1'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch2'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1500,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>500,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch3'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1300,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch4'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1300,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch5'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1300,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch6'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1300,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch7'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1300,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
                'ch8'=>[
                    'open_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1300,
                    ],
                    'close_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>1,
                        'max_value'=>2000,
                        'current_value'=>1000,
                    ],
                ],
            ];
            $vehicleConfig['vehicle_config_detail'] = json_encode($channelConfig);
            $vehicleConfig['camera_type'] = $data['camera_type'];
            VehicleConfig::where('vehicle_id',$id)->update($vehicleConfig);

        }
        $vehicleConfig['camera_type'] = $data['camera_type'];
        VehicleConfig::where('vehicle_id',$id)->update($vehicleConfig);

        $vehicle->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function processingAlarm($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['id'];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $alarmVehicle = AlarmVehcle::where('id', $id)->first();
        if(!$alarmVehicle){
            return ReponseData::reponseFormat(2000,'未找到该报警记录');
        }
        $alarmVehicle->status = 1;
        $alarmVehicle->save();
        $vehicle = Vehicle::where('id', $alarmVehicle['vehicle_id'])->first();
        if($vehicle){
            $vehicle->update(['status'=>1]);
        }
        return ReponseData::reponseFormat(200,'处理成功!');
    }
    public function processingAlarmList($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
//        $status = $request['status'] ??  null;
        $agentId = $request['agent_id'] ?? null;
        if(!$agentId){
            return ReponseData::reponseFormat(2000,'代理id必传');
        }
//        if(!$status){
//            return ReponseData::reponseFormat(2000,'status必传');
//        }
        $list = AlarmVehcle::where('agent_id',$agentId)
            ->orderBy('id', 'desc') // id 倒序，最新在前
            ->limit(50)
            ->get();
        $uids = $list->pluck('uid');
        $userUserName = Cuser::query()
            ->whereIn('id', $uids)
            ->pluck('show_id', 'id')
            ->toArray();
        $respList = [
            'on_dispose'=>[],
            'off_dispose'=>[],
        ];
        foreach($list as $value){
            $vehicle = Vehicle::select('vehicle_image','vehicle_name')->where('id', $value->vehicle_id)->first();
            $value['venue_id'] = $value['war_id'];
            $value['venue_name'] = $value['war_zone_name'];
            $value['user_name'] = $userUserName[$value['uid']] ?? '';
            $value['user_name'] = strval($value['user_name']);

            if($value['status'] == 1){
                $respList['on_dispose'][] = $value;
            }else{
                $respList['off_dispose'][] = $value;
            }
            if($vehicle){
                $value['vehicle_image'] = $vehicle['vehicle_image'];
                $value['vehicle_name'] = $vehicle['vehicle_name'];
            }else{
                $value['vehicle_image'] = '';
                $value['vehicle_name'] = '';
            }

            unset($value['war_id']);
            unset($value['war_zone_name']);
        }
        return ReponseData::reponseFormatList(200,'获取成功!',$respList);
    }

    public function processingAlarmDelete($request)
    {
//        $request = $this->setvice->decrypt($request['data']);

        $id = $request['id'];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $alarmVehicle = AlarmVehcle::where('id', $id)->first();
        if(!$alarmVehicle){
            return ReponseData::reponseFormat(2000,'未找到该报警记录');
        }
        $alarmVehicle->delete();

        return ReponseData::reponseFormat(200,'删除成功!');
    }

    public function vehicleDetailReset($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['id'];
        $type = $request['type'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        if(!$type){
            return ReponseData::reponseFormat(2000,'车辆重置配置必须传');
        }

        $vehicle = Vehicle::where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2007,'未找到该车辆!');

        }
        $vehicleConfig = VehicleConfig::where('vehicle_id',$id)->first();
        if(!$vehicleConfig){
            return ReponseData::reponseFormat(2000,'未找到该配置!');
        }
        $updateVehicleConfig = json_decode($vehicleConfig['vehicle_config_detail'],true);
        $default = [
            'open_value'=>[
                'mini_value'=>1,
                'max_value'=>2000,
                'current_value'=>1500,
            ],
            'close_value'=>[
                'mini_value'=>1,
                'max_value'=>2000,
                'current_value'=>500,
            ],
            'center_value'=>[
                'mini_value'=>1,
                'max_value'=>2000,
                'current_value'=>1000,
            ],
        ];
        if($type >= 3){
            $default['close_value']['current_value'] = 700;
        }

        switch ($type) {
            case 1:
                $updateVehicleConfig['ch1'] = $default;
                $returnData = $updateVehicleConfig['ch1'];
                 break;
            case 2:
                $updateVehicleConfig['ch2'] = $default;
                $returnData = $updateVehicleConfig['ch2'];
                break;

            case 3:
                $updateVehicleConfig['ch3'] = $default;
                $returnData = $updateVehicleConfig['ch3'];
                break;

            case 4:
                $updateVehicleConfig['ch4'] = $default;
                $returnData = $updateVehicleConfig['ch4'];
                break;

            case 5:
                $updateVehicleConfig['ch5'] = $default;
                $returnData = $updateVehicleConfig['ch5'];
                break;
            case 6:
                $updateVehicleConfig['ch6'] = $default;
                $returnData = $updateVehicleConfig['ch6'];
                break;
            case 7:
                $updateVehicleConfig['ch7'] = $default;
                $returnData = $updateVehicleConfig['ch7'];
                break;
            case 8:
                $updateVehicleConfig['ch8'] = $default;
                $returnData = $updateVehicleConfig['ch8'];
                break;

        }
        $vehicleConfig->update(['vehicle_config_detail'=>$updateVehicleConfig]);

        return ReponseData::reponseFormatList(200,'重置成功',$returnData);
    }

    public function processingAlarmCreate($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'id' => $request['id'] ?? null,
            'text' => $request['text'] ?? null,
            'order_no' => $request['order_no'] ?? null,
            'uid' => $request['uid'] ?? null,
        ];

        if(!$data['id']){
            return ReponseData::reponseFormat(2000,'id必传');
        }

        if(!$data['text']){
            return ReponseData::reponseFormat(2000,'内容必传');
        }

        $vehicle = Vehicle::where('id', $data['id'])->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'未找到该车辆!');
        }

        $insertData = [
            'text' => $data['text'],
            'vehicle_id' => $data['id'],
            'vehicle_name' => $vehicle['vehicle_name'],
            'agent_id' => $vehicle['agent_id'],
            'war_id' => $vehicle['venue_id'],
            'war_zone_name' => $vehicle['venue_name'],
            'vehicle_image' => $vehicle['vehicle_image'],
            'order_no' => $data['order_no'],
            'uid' => $data['uid'],
            'status' => 0,
        ];

        $key = 'processing_'.$data['order_no'].'_'.$data['uid'];
        $ret = Redis::set($key, '1','ex','2','nx');
        if(!$ret){
            return ReponseData::reponseFormat(2000,'请勿重复点击哦');
        }
        $drivingRecord = DrivingRecord::where('order_no', $data['order_no'])->first();
        if(!$drivingRecord){
            return ReponseData::reponseFormat(2000,'未找到该订单!');
        }
        $user = Cuser::where('id',$drivingRecord['uid'])->first();
        if(!$user){
            return ReponseData::reponseFormat(2000,'未找到用户!');
        }
        $time = time();
        $startTime = $drivingRecord['start_time'];
        if($drivingRecord['reservation_status'] == 4 || $drivingRecord['reservation_status'] == 5){
            return  ReponseData::reponseFormat(2000,'订单已完成 不允许报修');
        }
        if($startTime > 0) {
            $surplusTime = $time - $startTime;
            if ($surplusTime <= 10) {
                Redis::del($drivingRecord['transmitter_id']); //解绑绑定车辆接收机、发射机id
                $drivingRecord->update([
                    'reservation_status' => 5,
                    'end_time' => $time,
                    'transmitter_id' => '0',//释放发射机id
                ]);
                $receiverJson = json_decode(Redis::get($drivingRecord['receiver_id'] . '_receiver'), true);
                $receiverJson['transmitter_id'] = '0';
                $receiverJson['transmitter_host_port'] = '';
                Redis::set($drivingRecord['receiver_id'] . '_receiver', json_encode($receiverJson));
                try {
                    if ($drivingRecord['payment_type'] == 1) {
                        WalletService::safeAdjust([
                            'uid' => $user->id,
                            'type' => CuserWalletLog::TypeReturn,
                            'type_name' => '报修退还',
                            'make_order_no' => $drivingRecord['order_no'],
                            'amount' => $drivingRecord['payment_amount'],
                            'venue' => $user->special_area_name,
                            'special_area' => $user->special_area,
                        ]);
                    }
                    if ($drivingRecord['payment_type'] == 2) {
                        WalletService::safeAdjustEnergy([
                            'uid' => $user->id,
                            'type' => CuserWalletLog::TypeReturn,
                            'type_name' => '报修退还',
                            'make_order_no' => $drivingRecord['order_no'],
                            'amount' => $drivingRecord['payment_amount'],
                            'venue' => $user->special_area_name,
                            'special_area' => $user->special_area,
                        ]);
                    }

                    $drivingRecord->update([
                        'reservation_status' => 4,
                        'end_time' => $time,
                        'transmitter_id' => '0',//释放发射机id
                        'payment_amount' => 0,
                    ]);

                    Redis::del('vehicle'.$vehicle['id']); //上报警告结束解锁车

                } catch (\Exception $e) {
                    return ReponseData::reponseFormat(2000, $e->getMessage());
                }
            } else {
                $billing_rules = json_decode($drivingRecord['billing_rules'], true);
                if (!$billing_rules) {
                    return ReponseData::reponseFormat(2000, '订单错误');
                }
                $rulesAmount = $billing_rules['battery']; //金额
                $rulesTime = $billing_rules['time'] * 60; //时间
                $startTime = $drivingRecord['start_time'];
                if ($drivingRecord['billing_method'] != 1) {
                    $count = ($time - $startTime) / $rulesTime + 1; //已进行次数
                    $shouldTime = $startTime + ($rulesTime * intval($count)); //当前阶段应该结束时间
                    $shouldTime2 = $shouldTime - $time; //阶段剩余多少时间
                    $shouldTime3 = $rulesTime - $shouldTime2; //阶段时间-剩余时间
                    $num = $shouldTime3 / $rulesTime;
                    $returnAmount = intval($rulesAmount * ($shouldTime2 / $rulesTime)); //返回金额 = 阶段金额*当前剩余时间/阶段时间
//                        }
                    if($num >= 0.3 && $num < 0.5){
                        $returnAmount = intval($rulesAmount * 0.5);
                    }
                    $totalAmount = ($billing_rules['battery'] * $count);
                    if($returnAmount == $drivingRecord['payment_amount'] && $count == 1){
                        $returnAmount = 0;
                    }
                    if($count > 1 &&  $totalAmount > $drivingRecord['payment_amount']){ //继续驾驶没成功
                        $returnAmount = 0;
                    }


                } else {

                    $count =  1; //按次固定一次
                    $shouldTime = $startTime + ($rulesTime * $count); //当前阶段应该结束时间
                    $shouldTime2 = $shouldTime - $time; //阶段剩余多少时间=未使用时间
                    $shouldTime3 = $rulesTime - $shouldTime2; //阶段时间-剩余时间=已使用时间
                    $num = $shouldTime3 / $rulesTime;
                    $p3 = $rulesAmount * 0.3;  // 中间30%
                    $p3_last = $rulesAmount * 0.3; // 最后30%
                    $returnAmount = 0;
                    $p1 = $rulesAmount * 0.2;

                    if($num < 0.9) { //超90直接不退钱

                        $returnAmount = intval($rulesAmount * ($shouldTime2 / $rulesTime)); //返回金额 = 阶段金额*当前剩余时间/阶段时间
                        if(($time - $startTime) <= 15){
                            $returnAmount = intval($rulesAmount) - 2; // 上车就扣2电池
                        }
                        if((intval($rulesAmount) - $returnAmount) <= 2){
                            $returnAmount = intval($rulesAmount) - 2; // 上车就扣2电池
                        }


                        // 只用了前40%区间，扣4成，剩余6成可退
//                        $returnAmount = intval($p3 + $p3_last);
//                        if($num <= 0.2){ // 如果时长不到20%
//                            $returnAmount = intval($p1 + $p3 + $p3_last); //总共扣20%的钱
//                        }
//                        if ($num >= 0.4 && $num < 0.7) {
//                            // 用完前40%+中间30%，共扣7成，最后3成可退
//                            $returnAmount = intval($p3_last);
//                        }
                    }

//                    $shouldTime = $startTime + $rulesTime; //当前阶段应该结束时间
//                    $shouldTime2 = $shouldTime - $time; //阶段剩余多少时间
//                    $shouldTime3 = $rulesTime - $shouldTime2; //阶段时间-剩余时间=已使用时间
//                    $num = $shouldTime3 / $rulesTime;
//                    $p3 = $rulesAmount * 0.3;  // 中间30%
//                    $p3_last = $rulesAmount * 0.3; // 最后30%
//                    $returnAmount = intval($rulesAmount * ($shouldTime2 / $rulesTime)); //返回金额 = 阶段金额*当前剩余时间/阶段时间
                }

                if ($drivingRecord['payment_type'] == 1) {
                    WalletService::safeAdjust([
                        'uid' => $user->id,
                        'type' => CuserWalletLog::TypeReturn,
                        'type_name' => '报修退还',
                        'make_order_no' =>$drivingRecord['order_no'],
                        'amount' => $returnAmount,
                        'venue' => $user->special_area_name,
                        'special_area' =>$user->special_area,
                    ]);
                    //代理商收入
                    $agentWallet = AgentWallet::getBalance($drivingRecord['agent_id']);
                    $balance = $agentWallet['balance'];
                    $updateQuery = AgentWallet::where(['agent_id' => $drivingRecord['agent_id']]);
                    $updateAmount = $drivingRecord['payment_amount'] - $returnAmount;

                    $affected = $updateQuery->update(['balance' => DB::raw("balance+{$updateAmount}")]);
                    if ($affected != 1) {
                        Log::info("结束驾驶收入金额： {$updateAmount}, 增加失败： {$agentWallet['balance']}");
                    }
                    $afterBalance = $balance + $drivingRecord['payment_amount'] - $returnAmount;
                    AgentWalletLog::create([
                        'agent_id' => $drivingRecord['agent_id'],
                        'type' => 1,
                        'type_name' => '收入',
                        'make_order_no'=>$drivingRecord['order_no'],
                        'amount' =>  $drivingRecord['payment_amount'] - $returnAmount,
                        'balance' => $afterBalance,
                        'time' => time(),
                    ]);
                }
                if ($drivingRecord['payment_type'] == 2) {
                    WalletService::safeAdjustEnergy([
                        'uid' => $user->id,
                        'type' => CuserWalletLog::TypeReturn,
                        'type_name' => '报修退还',
                        'make_order_no' => $drivingRecord['order_no'],
                        'amount' => $returnAmount,
                        'venue' => $user->special_area_name,
                        'special_area' => $user->special_area,
                    ]);
                }
                Redis::del($drivingRecord['transmitter_id']); //解绑绑定车辆接收机、发射机id
                $drivingRecord->update([
                    'reservation_status' => 4,
                    'end_time' => $time,
                    'transmitter_id' => '0',//释放发射机id
                    'payment_amount' => $drivingRecord['payment_amount'] - $returnAmount,
                ]);
                $receiverJson = json_decode(Redis::get($drivingRecord['receiver_id'] . '_receiver'), true);
                $receiverJson['transmitter_id'] = '0';
                $receiverJson['transmitter_host_port'] = '';
                Redis::set($drivingRecord['receiver_id'] . '_receiver', json_encode($receiverJson));

            }
        }else{
            $drivingRecord->update([
                'reservation_status' => 5,
                'transmitter_id' => '0',//释放发射机id
            ]);
        }
        $vehicle->update(['status'=>0,'vehicle_state' => 1]);
        Redis::del('vehicle'.$vehicle['id']); //上报警告结束解锁车

        AlarmVehcle::create($insertData);
        return  ReponseData::reponseFormat(200,'车辆报修提交成功!');
    }
    public function updateVehicleBattery($request)
    {
        $vehicle_id =  $request['vehicle_id'] ?? null;

        if(!$vehicle_id){
            return ReponseData::reponseFormat(2000,'车辆id必须传');
        }

        $vehicle = Vehicle::where('id',$vehicle_id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'未找到该车辆');
        }

        $vehicle_battery= $request['vehicle_battery'] ?? $vehicle['vehicle_battery'];
        $vehicle->update(['vehicle_battery'=>$vehicle_battery]);

        return ReponseData::reponseFormat(200,'更新成功');
    }
}
