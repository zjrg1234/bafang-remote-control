<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $vehicle_id
 * @property string $direction_dynamics
 * @property string $accelerator_dynamics
 * @property string $direction_center
 * @property string $accelerator_center
 * @property integer $mixed_control
 * @property string $video_definition
 * @property integer $rear_camera_type
 * @property integer $operation_mode
 * @property string $vehicle_config_detail
 * @property integer $reverse_left_right
 * @property integer $reverse_up_down
 * @property integer $reverse_rotation
 * @property integer $change_ui_control
 * @property string $app_transmitter_id
 * @property string $created_at
 * @property string $updated_at
 */
class PersonVehicleConfig extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'person_vehicle_config';

    /**
     * @var array
     */
    protected $fillable = ['vehicle_id', 'direction_dynamics', 'accelerator_dynamics', 'direction_center', 'accelerator_center', 'mixed_control', 'video_definition', 'rear_camera_type', 'operation_mode', 'vehicle_config_detail', 'reverse_left_right', 'reverse_up_down', 'reverse_rotation', 'change_ui_control', 'app_transmitter_id','default_camera_clarity','camera_type', 'created_at', 'updated_at'];
}
