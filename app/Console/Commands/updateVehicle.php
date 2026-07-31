<?php

namespace App\Console\Commands;

use App\Models\AgentVenue;
use App\Models\CuserAgent;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use function Symfony\Component\Translation\t;

class updateVehicle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'update-vehicle';

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
            $key = Redis::get('close');
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
                $agentIds = CuserAgent::pluck('id'); //取出所有代理商id
                $venueIds = AgentVenue::whereIn('agent_id', $agentIds)->pluck('id'); //取出所有代理商下面的场地
                $vehicles = Vehicle::whereIn('venue_id', $venueIds)->get(); //取出所有车辆
                if ($vehicles->isNotEmpty()) {
                    foreach ($vehicles as $vehicle) {
                        $status = Redis::get($vehicle['receiver_id'] . '_receiver');
                        $vehicle_state = Redis::get('vehicle'.$vehicle['id']);

                        if (isset($status) && $vehicle['vehicle_state'] === 0) {
                            $json = json_decode($status, true);
                            if(isset($json['receiver_id'])) {
                                $vehicle['vehicle_state'] = 1;
                                $vehicle->save();
                            }
                        }
                        if (isset($status) && !$vehicle_state){
                            $json = json_decode($status, true);
                            if(empty($json['receiver_id'])){
                                $vehicle['vehicle_state'] = 0;
                                $vehicle->save();
                            }
                        }
                        if(!$status && !$vehicle_state){
                            $vehicle['vehicle_state'] = 0;
                            $vehicle->save();
                        }
                    }
                }
            }else{
                Log::info( '手动结束更新车辆信息');
                return 0;
            }


            $this->info('更新车辆信息');
            sleep(3);
        }
        return 0;
    }
}
