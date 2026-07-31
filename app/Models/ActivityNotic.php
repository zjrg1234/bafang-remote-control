<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $type
 * @property integer $special_area
 * @property string $special_area_name
 * @property string $activity_title
 * @property string $activity_image
 * @property integer $is_index
 * @property integer $is_discover
 * @property integer $activity_type
 * @property integer $status
 * @property integer $sort
 * @property string $created_at
 * @property string $updated_at
 */
class ActivityNotic extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_notice';

    /**
     * @var array
     */
    protected $fillable = ['type', 'special_area', 'index_image','discover_image','content','remark','special_area_name', 'activity_title', 'activity_image', 'is_index', 'is_discover', 'activity_type', 'status', 'sort', 'created_at', 'updated_at'];
}
