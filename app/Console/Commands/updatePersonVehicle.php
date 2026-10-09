<?php

namespace App\Console\Commands;

use App\Models\AgentVenue;
use App\Models\CuserAgent;
use App\Models\PersonVehicle;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class updatePersonVehicle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-person-vehicle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        while (true) {
            $key = Redis::get('close-person');
//            $agentIds = CuserAgent::pluck('id');
//            $venueIds = AgentVenue::whereIn('agent_id',$agentIds)->pluck('id');
//            if(!$key) {
//                foreach ($venueIds as $venueId) {
//                    $vehicles = Vehicle::where('venue_id', $venueId)->get();
//                    if ($vehicles->isEmpty()) {
//                        continue;
//                    }
//                    foreach ($vehicles as $vehicle) {
//                        $status = Redis::get($vehicle['receiver_id'] . '_receiver');
//                        if (isset($status) && $vehicle['vehicle_state'] === 0) {
//                            $json = json_decode($status, true);
//                            if(isset($json['receiver_id'])) {
//                                $vehicle['vehicle_state'] = 1;
//                                $vehicle->save();
//                            }
//                        }
//                        if (isset($status)){
//                            $json = json_decode($status, true);
//                            if(empty($json['receiver_id'])){
//                                $vehicle['vehicle_state'] = 0;
//                                $vehicle->save();
//                            }
//                        }
//                        if(!$status){
//                            $vehicle['vehicle_state'] = 0;
//                            $vehicle->save();
//                        }
//                    }
//                }
//            }else{
//                Log::info( '手动结束更新车辆信息');
//                return 0;
//            }

            if(!$key) {
                PersonVehicle::chunkById(200, function ($vehicles) {
                    foreach ($vehicles as $vehicle) {
                        $status = Redis::get($vehicle->receiver_id . '_receiver');
                        $vehicle_state = Redis::get('person_start_driving_' . $vehicle->id);

                        // 记录预期的新状态，初始等于当前状态
                        $targetState = $vehicle->vehicle_state;

                        if ($status) {
                            $json = json_decode($status, true);
                            if (isset($json['receiver_id']) && $vehicle->vehicle_state === 0) {
                                $targetState = 1;
                            } elseif (empty($json['receiver_id']) && !$vehicle_state) {
                                $targetState = 0;
                            }
                        } elseif (!$vehicle_state) {
                            // 如果 $status 不存在且没开始驾驶
                            $targetState = 0;
                        }

                        // 核心优化点：只有当状态真正发生改变时，才执行数据库写入
                        // 这能帮你挡住 90% 以上无意义的 UPDATE 请求
                        if ($targetState !== $vehicle->vehicle_state) {
                            $vehicle->vehicle_state = $targetState;
                            $vehicle->save();
                        }
                    }
                });
            }else{
                Log::info( '手动结束更新车辆信息');
                return 0;
            }


            $this->info('更新车辆信息');
            sleep(3);
        }
    }
}
