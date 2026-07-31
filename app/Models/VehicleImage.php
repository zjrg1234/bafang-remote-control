<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $type
 * @property string $type_name
 * @property string $image
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class VehicleImage extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'vehicle_image';

    /**
     * @var array
     */
    protected $fillable = ['type', 'type_name', 'image', 'status', 'created_at', 'updated_at'];
}
