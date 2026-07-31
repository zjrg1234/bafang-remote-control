<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmVehcle extends Model
{
    use HasFactory;
    protected $table = 'alarm_vehicle';
    protected $fillable = [
        'vehicle_id',
        'agent_id',
        'war_zone_name',
        'war_id',
        'text',
        'status',
        'order_no',
        'uid',

    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
