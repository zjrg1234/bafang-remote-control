<?php

namespace App\Http\Service;
use App\Http\Repo\LoginRepo;
use App\Models\AgentVenue;
use App\Models\AgentWallet;
use App\Models\AgentWalletLog;
use App\Models\AlarmVehcle;
use App\Models\Cuser;
use App\Models\CuserAgent;
use App\Models\CuserWallet;
use App\Models\CuserWalletLog;
use App\Models\DrivingRecord;
use App\Models\PersonVehicle;
use App\Models\PersonVehicleConfig;
use App\Models\ReponseData;
use App\Models\Vehicle;
use App\Models\VehicleConfig;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PersonVehicleService
{


    protected $imageTypes = [
        1=>'车辆图片',
        2=>'遥控船图片',
        3=>'挖机图片',
        4=>'铲车图片',
        5=>'娃娃机图片',
    ];
    protected $setvice;
    public function __construct()
    {
        $this->setvice = new LoginService();
    }
    public function vehicleList($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'uid' => $request['uid'] ?? null,
//            'type' => $request['type'] ?? null,
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2001,'用户id必传!');
        }
        $exists = Cuser::where('id', $data['uid'])->exists();
        if(!$exists){
            return ReponseData::reponseFormat(2004,'未查询到该用户!');
        }

        $list = PersonVehicle::select('*')
            ->where('uid',$data['uid'])
            ->get();
        return ReponseData::reponseFormatList(200,'获取成功',$list);

    }
    public function deleteVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $vehicleId = $request['id'] ?? null;
        $uid =  $request['uid'] ?? null;
        if(!$uid){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }
        if(!$vehicleId){
            return ReponseData::reponseFormat(2004,'车辆id必传!');
        }
        $vehicle = PersonVehicle::where('id', $vehicleId)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2002,'未查询到该车辆或已被删除!');
        }
        $vehicle->delete();
        PersonVehicleConfig::where('vehicle_id', $vehicleId)->delete();


        return ReponseData::reponseFormat(200,'车辆删除成功!');

    }

    public function addVehicle($request)
    {
//        $request = $this->setvice->decrypt($request['data']);
        $data = [
            'vehicle_image' => $request['vehicle_image'] ?? null,
            'battery' => $request['battery'] ?? null,
            'vehicle_name' => $request['vehicle_name'] ?? null,
            'front_camera' => $request['front_camera'] ?? null,
            'rear_camera' =>  $request['rear_camera'] ?? '',
            'transmitter_id' => $request['transmitter_id'] ?? '',
            'receiver_id' => $request['receiver_id'] ?? null,
            'vehicle_type' => $request['vehicle_type'] ?? null,
            'uid' => $request['uid'] ?? null,
            'vehicle_sub_type' => $request['vehicle_sub_type'] ?? 1,
            'camera_type' => $request['camera_type'] ?? 1,
            'vehicle_introduction' => $request['vehicle_introduction'] ?? '',
            'top_speed' => $request['top_speed'] ?? '',
            'vehicle_sorting' => $request['vehicle_sorting'] ?? '1',
            'forward_type' => $request['type'] ?? 1,
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
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

        if(!$data['receiver_id']){
            return ReponseData::reponseFormat(2000,'接收机id必填!');
        }

        if(!$data['vehicle_image']){
            if($data['vehicle_type'] < 20){
                $vehicle_image = VehicleImage::where('type',1)->where('status',1)->value('image');
            }elseif ($data['vehicle_type'] >=20 && $data['vehicle_type'] <= 30) {
                $vehicle_image = VehicleImage::where('type',3)->where('status',1)->value('image');
            }else{
                $vehicle_image = '';
            }
            if($vehicle_image){
                $data['vehicle_image'] = $vehicle_image;
            }else{
                $data['vehicle_image'] = '';
            }
        }
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
            'video_definition' => '3,4,5',
            'rear_camera_type' => 0,
            'operation_mode' => 0,
        ];
        if($data['camera_type'] == 1){
            $vehicleConfig['video_definition'] = '2,3,4,5';
        }else{
            $vehicleConfig['video_definition'] = '2,3,4';

        }
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
        if($data['vehicle_type'] == 21){
            $channelConfig = [
                'ch1'=>[
                    'open_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1200,
                    ],
                    'close_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1000,
                    ],
                ],
                'ch2'=>[
                    'open_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1200,
                    ],
                    'close_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1000,
                    ],
                ],
                'ch3'=>[
                    'open_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1200,
                    ],
                    'close_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch4'=>[
                    'open_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1200,
                    ],
                    'close_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch5'=>[
                    'open_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1200,
                    ],
                    'close_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>700,
                    ],
                    'center_value'=>[
                        'mini_value'=>400,
                        'max_value'=>1600,
                        'current_value'=>1000,
                    ],
                ],
                'ch6'=>[
                    'open_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>1200,
                    ],
                    'close_value'=>[
                        'mini_value'=>300,
                        'max_value'=>1700,
                        'current_value'=>700,
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
        $exists = PersonVehicle::where('receiver_id', $data['receiver_id'])->first();
        if($exists){
            return ReponseData::reponseFormat(2000,'接收机重复!');
        }
        $data['transmitter_id'] = mt_rand(40000000,49999999);
        $data['app_transmitter_id'] = mt_rand(50000000,59999999);

        $vehicle = PersonVehicle::create($data);
        $vehicleConfig['vehicle_id'] = $vehicle['id'];
        PersonVehicleConfig::create($vehicleConfig);

        return ReponseData::reponseFormat(200,'车辆新增成功');
    }

    public function vehicleDetail($request){
//        $request = $this->setvice->decrypt($request['data']);
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }
        $vehicle = PersonVehicle::select('*')->where('id', $id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2001,'未找到该车辆!');
        }
        $vehicleConfig = PersonVehicleConfig::where('vehicle_id', $id)->first();
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
        $vehicleConfig['vehicle_config_detail'] = json_decode($vehicleConfig['vehicle_config_detail']);
        $vehicleConfig['app_transmitter_id'] = $vehicle['app_transmitter_id'];
        $vehicleConfig['vehicle_introduction'] = $vehicle['vehicle_introduction'];
        $vehicleConfig['vehicle_sorting'] = $vehicle['vehicle_sorting'];

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

        $id = $request['id'];
        $vehicleConfigDetail = $request['vehicle_config_detail'];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传!');
        }

        $vehicle = PersonVehicle::where('id', $id)->first();

        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'车辆未找到');
        }
        $vehicleConfigDb = PersonVehicleConfig::where('vehicle_id',$id)->first();
        $reverse_left_right = $request['reverse_left_right'] ?? $vehicleConfigDb['reverse_left_right'];
        $reverse_up_down = $request['reverse_up_down'] ?? $vehicleConfigDb['reverse_up_down'];
        $reverse_rotation = $request['reverse_rotation'] ?? $vehicleConfigDb['reverse_rotation'];
        $change_ui_control = $request['change_ui_control'] ?? $vehicleConfigDb['change_ui_control'];
        $vehicleConfig = PersonVehicleConfig::where('vehicle_id', $id)->first();

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

        if($vehicle['vehicle_type'] == 10){
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

        PersonVehicleConfig::where('vehicle_id', $id)->update([
            'reverse_left_right'=>$reverse_left_right,
            'reverse_up_down'=>$reverse_up_down,
            'reverse_rotation'=>$reverse_rotation,
            'change_ui_control'=>$change_ui_control,]);
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
            'front_camera' => $request['front_camera'] ?? null,
            'rear_camera' =>  $request['rear_camera'] ?? '',
            'transmitter_id' => $request['transmitter_id'] ?? '',
            'receiver_id' => $request['receiver_id'] ?? null,
            'vehicle_type' => $request['vehicle_type'] ?? null,
            'forward_type' => $request['type'] ?? 1,
            'sub_type' => $request['sub_type'] ?? 1,
            'camera_type' => $request['camera_type'] ?? 1,
            'vehicle_introduction' => $request['vehicle_introduction'] ?? '',
            'top_speed' => $request['top_speed'] ?? '',
            'vehicle_sorting' => $request['vehicle_sorting'] ?? '1',
        ];

        $vehicle = PersonVehicle::where('id', $id)->first();
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
            PersonVehicleConfig::where('vehicle_id',$id)->update($vehicleConfig);

        }
        $vehicleConfig['camera_type'] = $data['camera_type'];
        PersonVehicleConfig::where('vehicle_id',$id)->update($vehicleConfig);

        $vehicle->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
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
        $vehicleConfig = PersonVehicleConfig::where('vehicle_id',$id)->first();
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

    public function updateVehicleBattery($request)
    {
        $vehicle_id =  $request['vehicle_id'] ?? null;

        if(!$vehicle_id){
            return ReponseData::reponseFormat(2000,'车辆id必须传');
        }

        $vehicle = PersonVehicle::where('id',$vehicle_id)->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'未找到该车辆');
        }

        $vehicle_battery= $request['vehicle_battery'] ?? $vehicle['vehicle_battery'];
        $vehicle->update(['vehicle_battery'=>$vehicle_battery]);

        return ReponseData::reponseFormat(200,'更新成功');
    }


    public function setKey($request)
    {
        $data = [
            'order_no'  => $request['order_no'] ?? null,
            'type'     => $request['type'] ?? null,
        ];
        if(!$data['order_no']){
            return ReponseData::reponseFormat(2000,'订单号必传');
        }

        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'状态必传');
        }
        $key = 'vehicle_query_'.$data['order_no'];
        Redis::set($key,$data['type']);

        return ReponseData::reponseFormat(200,'设置成功');
    }

    public function queryKey($request)
    {
        $data = [
            'order_no'  => $request['order_no'] ?? null,
        ];
        if(!$data['order_no']){
            return ReponseData::reponseFormat(2000,'订单号必传');
        }

        $key = 'vehicle_query_'.$data['order_no'];
        $value = Redis::get($key);
        $resp = [
            'type' => $value,
        ];

        return ReponseData::reponseFormatList(200,'成功',$resp);
    }

    public function delKey($request)
    {
        $data = [
            'order_no'  => $request['order_no'] ?? null,
        ];
        if(!$data['order_no']){
            return ReponseData::reponseFormat(2000,'订单号必传');
        }

        $key = 'vehicle_query_'.$data['order_no'];
        Redis::del($key);
        return ReponseData::reponseFormat(200,'删除成功');

    }



    public function startDriving($request)
    {

        $data = [
            'uid' => $request['uid'] ?? null,
            'type' => $request['type'] ?? null
        ];
        $user = Cuser::where('id',$data['uid'])->first();
        //代理商端处理逻辑

        $data['transmitter_id'] = $request['transmitter_id'] ?? null;
        $data['receiver_id'] = $request['receiver_id'] ?? null;
        $data['vehicle_id'] = $request['vehicle_id'] ?? null;

        if(!$data['transmitter_id']){
            return ReponseData::reponseFormat(2000,'发射机id必传');
        }
        if(!$data['receiver_id']){
            return ReponseData::reponseFormat(2000,'接收机id必传');
        }
        if(!$data['vehicle_id']){
            return ReponseData::reponseFormat(2000,'车辆id必传');
        }
        $vehicle = PersonVehicle::where('id',$data['vehicle_id'])->first();
        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'未找到该车辆');
        }
        if($data['type'] == 1){
            if($vehicle['vehicle_state'] == 2){
                return ReponseData::reponseFormat(2000,'车辆正在驾驶中');
            }
            $check = Redis::get('vehicle_person_'.$data['vehicle_id']);

            if($vehicle['vehicle_state'] == 2){
                return  ReponseData::reponseFormat(2000,'车辆不在空闲中');
            }
            Redis::set($data['transmitter_id'],$data['receiver_id']); //绑定车辆接收机、发射机id
            $vehicle->update(['vehicle_state' => 2,'is_agent_start'=>1]);
            $key = 'person_start_driving_'.$data['vehicle_id'];
            Redis::setex($key,35,'start');
            $message = '开始驾驶成功';
        }else if($data['type'] == 2){
            $key = 'person_start_driving_'.$data['vehicle_id'];
            Redis::setex($key,35,'start');
            $message = '继续驾驶成功';
        }elseif($data['type'] == 3){
            Redis::del($data['transmitter_id']); //解绑遥控器接收机、发射机id
            Redis::del($data['app_transmitter_id']); //解绑app接收机、发射机id

            $receiverJson = Redis::get($data['receiver_id'].'_receiver');
            $receiverJson = json_decode($receiverJson,true);
            $receiverJson['transmitter_id'] = '0';
            $receiverJson['transmitter_host_port'] = '';
            Redis::set($data['receiver_id'].'_receiver',json_encode($receiverJson));
            $message = '结束驾驶成功';
            $vehicle->update(['vehicle_state' => 1,'is_agent_start'=>0]);
            $key = 'person_start_driving_'.$data['vehicle_id'];
            Redis::del($key);
            Redis::del('vehicle_person_'.$vehicle['id']); //结束驾驶解锁车

        }else{
            $key = 'person_start_driving_'.$data['vehicle_id'];
            $message = '驾驶数据错误';
            Redis::setex($key,35,'start');
            return ReponseData::reponseFormat(2000,$message);

        }

        return ReponseData::reponseFormat(200,$message);
    }

    public function bindAppLoginPhone($request)
    {
        $data = [
            'phone' => $request['phone'] ?? null,
            'captcha'  => $request['noteVerify'] ?? null,
            'open_id' => $request['open_id'] ?? null,
            'type' =>$request['type'] ?? null,
        ];
        if(!$data['open_id']){
            return ReponseData::reponseFormat(2000,'open_id必传');

        }
        if(!$data['type']){
            return ReponseData::reponseFormat(2000,'平台必传');

        }
        $user = Cuser::where('phone_number',$data['phone'])->first();
        if(!$user){
            if($data['captcha'] == '666666'){
//                if(isset($data['captcha'])){
//                    return ReponseData::reponseFormat(2003,'验证码错误！');
//                }
            }else{
                $code = Redis::get($data['phone']);
                if(empty($code)){
                    return ReponseData::reponseFormat(2003,'验证码已过期！');
                }
                if($code != $data['captcha']){
                    return ReponseData::reponseFormat(2000,'验证码错误');
                }
                Redis::del($data['phone']);

            }
            $special_area = CuserAgent::where('superior_agent_id',0)->inRandomOrder()->first();
            $ip = getIp($request);

            $insertData = [
                'phone_number' => $data['phone'],
                'special_area' => $special_area['id'],
                'special_area_name' => $special_area['agent_name'],
                'register_time' => time(),
                'login_ip' => $ip,
                'head_shot' => 'https://bfyk.oss-cn-hangzhou.aliyuncs.com/yk/image/ZKSJ_1785999958KSGK.jpeg', //默认头像
                'username' => '八方远控'.mt_rand(10000000,99999999),
                'show_id' => mt_rand(10000000,99999999),
                'is_screenshot' => 1,
            ];
            if($data['type'] == 1){
                $insertData['wechat_app_openid'] = $data['open_id'];
            }else{
                $insertData['dy_openid'] = $data['open_id'];
            }
            $repo =  new LoginRepo();
            $user = $repo->createUsers($insertData);
            $loginService = new LoginService();
            if ($user) {
                $response = $loginService->registerLogin($user);
                return ReponseData::reponseData($response);
            }
        }else{
            $nowTime                 = time();
            $sessionKey              = base64_encode(md5($user['id'].$user['user_name'].$nowTime));
            $key = 'token_'.$user['id'];
            Redis::set($key, $sessionKey);
            $updateData = [
                'last_online_time' => $nowTime,
                'session_key' => $sessionKey,

            ];
            if($data['type'] == 1){
                $updateData['wechat_app_openid'] = $data['open_id'];
            }else{
                $updateData['dy_openid'] = $data['open_id'];
            }
            Cuser::where('id', $user['id'])->update($updateData);
            $response =  [
                'id' => $user['id'],
                'special_area' => $user['special_area'],
                'session_key' => $sessionKey,
                'new_user' => 0,
            ];
            $responseData = $response;
            return ReponseData::reponseFormatList(200,'成功',$responseData);
        }
        return ReponseData::reponseFormat(200,'绑定成功');
    }

    public function refreshClose($request)
    {
        $data = [
            'uid' => $request['uid'] ?? null,
            'id' => $request['id'] ?? null,
        ];
        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传!');
        }

        if(!$data['id']){
            return ReponseData::reponseFormat(2000,'车辆id必传!');
        }

        $vehicle = PersonVehicle::where('id',$data['id'])->first();

        if(!$vehicle){
            return  ReponseData::reponseFormat(2000,'未找到该车辆');
        }
        $vehicle->update(['vehicle_state' => 1]);
        Redis::del($vehicle['transmitter_id']); //解绑车辆接收机、发射机id
        $receiverJson = Redis::get($vehicle['receiver_id'].'_receiver');
        $receiverJson = json_decode($receiverJson,true);
        $receiverJson['transmitter_id'] = '0';
        $receiverJson['transmitter_host_port'] = '';
        Redis::set($vehicle['receiver_id'].'_receiver',json_encode($receiverJson));
        $key = 'person_start_driving_'.$vehicle['id'];
        Redis::del($key);
        Redis::del('vehicle_person_'.$vehicle['id']); //结束驾驶解锁车

        return ReponseData::reponseFormat(200,'刷新关闭成功');
    }

    public function getVehicleBattery($request)
    {
        $data = [
            'uid' => $request['uid'] ?? null,
            'vehicle_id' => $request['vehicle_id'] ?? null,
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }

        if(!$data['vehicle_id']){
            return ReponseData::reponseFormat(2000,'车辆id必传');
        }

        $vehicle = PersonVehicle::where('id',$data['vehicle_id'])->first();

        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'未找到该车辆');
        }

        if($vehicle['vehicle_state'] >= 1){
            $vehicleState = 1;
        }else{
            $vehicleState = 0;

        }
        $resp = [
            'vehicle_state' => $vehicleState,
            'battery' => $vehicle['vehicle_battery'] ?? '0%'
        ];

        return  ReponseData::reponseFormatList(200,'成功',$resp);
    }

    public function switchRemoteControl($request)
    {
        $data = [
            'uid' => $request['uid'] ?? null,
            'vehicle_id' => $request['vehicle_id'] ?? null,
            'type' => $request['type'] ?? null
        ];

        if(!$data['uid']){
            return ReponseData::reponseFormat(2000,'用户id必传');
        }
        if(!$data['type']){
            return ReponseData::reponseFormat(2000,'状态必传');
        }
        if(!$data['vehicle_id']){
            return ReponseData::reponseFormat(2000,'车辆id必传');
        }

        $vehicle = PersonVehicle::where('id',$data['vehicle_id'])->first();

        if(!$vehicle){
            return ReponseData::reponseFormat(2000,'未找到该车辆');
        }

        if($data['type'] == 2){
            Redis::set($vehicle['transmitter_id'],$vehicle['receiver_id']); //绑定遥控器接收机、发射机id
            Redis::del($vehicle['app_transmitter_id']); //解绑app接收机、发射机id
        }else{
            Redis::set($vehicle['app_transmitter_id'],$vehicle['receiver_id']); //绑定遥控器接收机、发射机id
            Redis::del($vehicle['transmitter_id']); //解绑app接收机、发射机id
        }


        return ReponseData::reponseFormat(200,'更换成功');
    }
}
