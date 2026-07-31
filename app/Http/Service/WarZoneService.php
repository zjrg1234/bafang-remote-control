<?php

namespace App\Http\Service;

use App\Models\CuserAgent;
use App\Models\ReponseData;
use App\Models\Vehicle;

class WarZoneService
{

    public function vehicleList($request)
    {
        $data = [
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
            'name' => $request['name'] ?? null,
            'vehicle_state' => $request['vehicle_state'] ?? null,
            'receiver_name' => $request['receiver_name'] ?? null,
            'transmitter_id' => $request['transmitter_id'] ?? null,
            'binding_state' => $request['binding_state'] ?? null,

        ];


        $list = Vehicle::select('*');
        if($data['name']){
            $list->where('vehicle_name', 'like', '%'.$data['name'].'%');
        }
        if($data['receiver_name']){
            $list->where('receiver_id',$data['receiver_name']);
        }
        if($data['transmitter_id']){
            $list->where('transmitter_id',$data['transmitter_id']);
        }

        if(isset($data['binding_state']) && $data['binding_state'] == 1){
            $list->where('transmitter_id','!=','');
        }else if(isset($data['binding_state']) && $data['binding_state'] == 2){
            $list->where('transmitter_id','');
        }
        if($data['vehicle_state']){
            $list->where('vehicle_state',$data['vehicle_state']);
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
        $agentIds = collect($rows->items())->pluck('agent_id')->unique()->filter()->toArray();
        $userUserAgentName = CuserAgent::query()
            ->whereIn('id', $agentIds)
            ->pluck('agent_name', 'id')
            ->toArray();
        foreach($rows as $row){
            if($row->transmitter_id != ''){
                $row['binding_name'] = '遥控器';
            }else{
                $row['binding_name'] = 'App';
            }
            $row['venue_name'] = $userUserAgentName[$row['agent_id']] ?? $row['venue_name'];
            if($row['vehicle_type'] >= 20){
                $row['vehicle_type'] = 20;
            }

        }
        return ReponseData::reponsePaginationFormat($rows);
    }
}
