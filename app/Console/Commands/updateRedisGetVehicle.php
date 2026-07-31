<?php

namespace App\Console\Commands;

use App\Models\AgentVenue;
use App\Models\CuserAgent;
use App\Models\DrivingRecord;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class updateRedisGetVehicle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-redis-get-vehicle';

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
        $agentIds = CuserAgent::pluck('id'); //取出所有代理商id
        $venueIds = AgentVenue::whereIn('agent_id', $agentIds)->pluck('id'); //取出所有代理商下面的场地
        $vehicles = Vehicle::whereIn('venue_id', $venueIds)->get(); //取出所有车辆
        if ($vehicles->isNotEmpty()) {
            foreach ($vehicles as $vehicle) {
                $vehicle_state = Redis::get('vehicle'.$vehicle['id']);
                $driving_record = DrivingRecord::where('vehicle_id', $vehicle['id'])->where('reservation_status',3)->exists();
                if($vehicle_state && !$driving_record){ //每分钟检测一次 如果没有驾驶中的订单 并且 驾驶缓存key还在的话 干掉key
                    Redis::del('vehicle'.$vehicle['id']);
                    $this->info('查到有驾驶状态key卡住，删除缓存：' .'vehicle'.$vehicle['id'] );
                }
            }
        }
        $this->info('清理驾驶缓存key成功' );

    }
}
