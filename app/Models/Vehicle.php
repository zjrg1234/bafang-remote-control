<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;
    protected $table = 'vehicle';

    protected $fillable = [
        'agent_id',
        'venue_id',
        'venue_name',
        'vehicle_type',
        'vehicle_image',
        'vehicle_name',
        'battery_time',
        'vehicle_introduction',
        'top_speed',
        'front_camera',
        'rear_camera',
        'transmitter_id',
        'receiver_id',
        'vehicle_sorting',
        'status',
        'vehicle_state',
        'password',
        'is_password',
        'vehicle_battery',
        'battery',
        'app_transmitter_id',
        'reverse_left_right',
        'forward_type',
        'reverse_up_down',
        'reverse_rotation',
        'change_ui_control',
        'is_agent_start',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
