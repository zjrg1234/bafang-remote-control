<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentVenue extends Model
{
    protected $table = 'agent_venue';
    use HasFactory;
    protected $fillable = [
        'agent_id',
        'venue_name',
        'venue_image',
        'agent_name',
        'start_time',
        'end_time',
        'vehicle_id',
        'deposit',
        'vehicle_count',
        'support_status',
        'online_vehicle',
        'sorting',
        'provinces',
        'labels',
        'venue_config',
        'venue_introduction',
        'area',
        'city',
        'label_id'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function drivingRecords()
    {
        // 参数说明：目标模型, 目标表的外键, 当前表的主键
        return $this->hasMany(DrivingRecord::class, 'venue_id', 'id');
    }

    public function vehicles()
    {
        // 假设车辆表对应的模型是 Vehicle，且车辆表中有关联场地的 venue_id
        return $this->hasMany(Vehicle::class, 'venue_id', 'id');
    }
}
