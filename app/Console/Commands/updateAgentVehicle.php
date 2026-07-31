<?php

namespace App\Console\Commands;

use App\Models\DrivingRecord;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class updateAgentVehicle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-agent-vehicle';

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
        $vehicles = Vehicle::where('is_agent_start',1)->get();

        foreach ($vehicles as $vehicle) {
            $key = 'agent_start_driving_'.$vehicle['id'];
            $exists = Redis::get($key);
            if(!$exists){
                $vehicle['is_agent_start'] = 0;
                $vehicle['vehicle_state'] = 1;
                $vehicle->save();
            }
        }
        $this->info('更新代理商异常驾驶车辆状态');
    }
}
