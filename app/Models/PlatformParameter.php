<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $title
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 */
class PlatformParameter extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'platform_parameter';

    /**
     * @var array
     */
    protected $fillable = ['title', 'value', 'created_at', 'updated_at'];
}
