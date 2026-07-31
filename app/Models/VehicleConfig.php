<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleConfig extends Model
{
    use HasFactory;
    protected $table = 'vehicle_config';

    protected $fillable = [
        'vehicle_id',
        'direction_dynamics',
        'accelerator_dynamics',
        'direction_center',
        'accelerator_center',
        'video_definition',
        'rear_camera_type',
        'operation_mode',
        'vehicle_config_detail',
        'mixed_control',
        'camera_type',
        'default_camera_clarity',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
