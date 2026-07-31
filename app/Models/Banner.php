<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $image
 * @property string $url
 * @property string $type
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class Banner extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banner';

    /**
     * @var array
     */
    protected $fillable = ['name','image', 'url', 'type', 'status', 'created_at', 'updated_at'];
}
