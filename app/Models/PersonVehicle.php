<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $uid
 * @property integer $vehicle_type
 * @property integer $vehicle_sub_type
 * @property string $vehicle_image
 * @property string $vehicle_name
 * @property string $battery
 * @property string $front_camera
 * @property string $rear_camera
 * @property string $transmitter_id
 * @property string $receiver_id
 * @property integer $vehicle_state
 * @property string $vehicle_battery
 * @property integer $camera_type
 * @property integer $default_camera_clarity
 */
class PersonVehicle extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'person_vehicle';

    /**
     * @var array
     */
    protected $fillable = ['uid', 'vehicle_type', 'vehicle_sub_type', 'vehicle_image', 'vehicle_name', 'battery', 'front_camera', 'rear_camera', 'transmitter_id', 'receiver_id', 'vehicle_state', 'vehicle_battery', 'camera_type', 'default_camera_clarity'
    ,'forward_type','top_speed','vehicle_sorting','vehicle_introduction','app_transmitter_id'
    ];
}
