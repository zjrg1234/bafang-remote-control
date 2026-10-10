<?php

namespace App\Http\Service;

use App\Models\CuserAgent;
use App\Models\PersonVehicle;
use App\Models\ReponseData;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class WarZoneService
{

//    public function vehicleList($request)
//    {
//        $data = [
//            'page' => $request['page'] ?? 1,
//            'size' => $request['size'] ?? 10,
//            'name' => $request['name'] ?? null,
//            'vehicle_state' => $request['vehicle_state'] ?? null,
//            'receiver_name' => $request['receiver_name'] ?? null,
//            'transmitter_id' => $request['transmitter_id'] ?? null,
//            'binding_state' => $request['binding_state'] ?? null,
//
//        ];
//
//
//        $list = Vehicle::select('*');
//        if($data['name']){
//            $list->where('vehicle_name', 'like', '%'.$data['name'].'%');
//        }
//        if($data['receiver_name']){
//            $list->where('receiver_id',$data['receiver_name']);
//        }
//        if($data['transmitter_id']){
//            $list->where('transmitter_id',$data['transmitter_id']);
//        }
//
//        if(isset($data['binding_state']) && $data['binding_state'] == 1){
//            $list->where('transmitter_id','!=','');
//        }else if(isset($data['binding_state']) && $data['binding_state'] == 2){
//            $list->where('transmitter_id','');
//        }
//        if($data['vehicle_state']){
//            $list->where('vehicle_state',$data['vehicle_state']);
//        }
//        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);
//        $agentIds = collect($rows->items())->pluck('agent_id')->unique()->filter()->toArray();
//        $userUserAgentName = CuserAgent::query()
//            ->whereIn('id', $agentIds)
//            ->pluck('agent_name', 'id')
//            ->toArray();
//        foreach($rows as $row){
//            if($row->transmitter_id != ''){
//                $row['binding_name'] = '遥控器';
//            }else{
//                $row['binding_name'] = 'App';
//            }
//            $row['venue_name'] = $userUserAgentName[$row['agent_id']] ?? $row['venue_name'];
//            if($row['vehicle_type'] >= 20){
//                $row['vehicle_type'] = 20;
//            }
//
//        }
//        return ReponseData::reponsePaginationFormat($rows);
//    }

    public function vehicleList($request)
    {
        $data = [
            'page'           => $request['page'] ?? 1,
            'size'           => $request['size'] ?? 10,
            'name'           => $request['name'] ?? null,
            'vehicle_state'  => $request['vehicle_state'] ?? null,
            'receiver_name'  => $request['receiver_name'] ?? null,
            'transmitter_id' => $request['transmitter_id'] ?? null,
            'binding_state'  => $request['binding_state'] ?? null,
        ];

        // ================= 1. 定义统一查询条件 (闭包) =================
        $applyFilters = function ($query) use ($data) {
            if ($data['name']) {
                $query->where('vehicle_name', 'like', '%' . $data['name'] . '%');
            }
            if ($data['receiver_name']) {
                $query->where('receiver_id', $data['receiver_name']);
            }
            if ($data['transmitter_id']) {
                $query->where('transmitter_id', $data['transmitter_id']);
            }

            if (isset($data['binding_state']) && $data['binding_state'] == 1) {
                $query->where('transmitter_id', '!=', '');
            } else if (isset($data['binding_state']) && $data['binding_state'] == 2) {
                $query->where('transmitter_id', '');
            }

            if ($data['vehicle_state']) {
                $query->where('vehicle_state', $data['vehicle_state']);
            }
        };

        // ================= 2. 字段对齐策略 (核心难点) =================
        // 提取两张表共有的、且前端列表需要的字段。
        // 对于不共有的字段，使用 DB::raw 强行补齐，确保 UNION 时两边列数完全一样！

        // 表 A：公共场地车辆表
        $queryA = Vehicle::select(
            'id',
            'agent_id',                     // 代理商 ID
            'venue_name',                   // 场地名称
            'vehicle_type',
            'vehicle_image',
            'vehicle_name',
            'transmitter_id',
            'receiver_id',
            'vehicle_state',
            'top_speed',
            DB::raw("'vehicle' as source_table") // 注入一个虚拟字段，标记数据来源
        );
        $applyFilters($queryA);

        // 表 B：个人车辆表
        $queryB = PersonVehicle::select(
            'id',
            'uid as agent_id',               // 巧妙映射：把 uid 映射为 agent_id，保持结构一致
            DB::raw("'' as venue_name"),     // person_vehicle 没有场地名称，用空字符串补齐占位
            'vehicle_type',
            'vehicle_image',
            'vehicle_name',
            'transmitter_id',
            'receiver_id',
            'vehicle_state',
            'top_speed',
            DB::raw("'person_vehicle' as source_table") // 标记来源于个人车辆
        );
        $applyFilters($queryB);

        // ================= 3. 执行 UNION 与分页 =================
        $unionQuery = $queryA->unionAll($queryB);

        $rows = Vehicle::fromSub($unionQuery, 'combined_table')
            ->orderBy('id', 'desc')
            ->paginate($data['size'], ['*'], 'page', $data['page']);

        // ================= 4. 数据组合与格式化 =================
        // 因为前面的查询里，我们把 person_vehicle 的 uid 也 AS 成了 agent_id
        // 所以这里的 pluck 依然能顺利提取出 ID。
        $agentIds = collect($rows->items())->pluck('agent_id')->unique()->filter()->toArray();

        $userUserAgentName = [];
        if (!empty($agentIds)) {
            $userUserAgentName = CuserAgent::query()
                ->whereIn('id', $agentIds)
                ->pluck('agent_name', 'id')
                ->toArray();
        }

        foreach ($rows as $row) {
            // 判断绑定状态
            if ($row->transmitter_id != '') {
                $row['binding_name'] = '遥控器';
            } else {
                $row['binding_name'] = 'App';
            }

            // 🚨 针对不同来源表进行差异化处理
            if ($row->source_table == 'vehicle') {
                // 如果是场地车，拿代理商名称兜底
                $row['venue_name'] = $userUserAgentName[$row['agent_id']] ?? $row['venue_name'];
            } else {
                // 如果是个人车辆，uid 在 CuserAgent 表里肯定查不到对应的 agent_name
                // 此时可以手动给一个专属标识，或者去查询 User 表的用户昵称
                $row['venue_name'] = '个人专属车辆';
            }

            if ($row['vehicle_type'] >= 20) {
                $row['vehicle_type'] = 20;
            }
        }

        return ReponseData::reponsePaginationFormat($rows);
    }
}
