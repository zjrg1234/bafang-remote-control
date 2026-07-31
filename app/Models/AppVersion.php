<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $version_mark
 * @property string $version_coding
 * @property integer $type
 * @property string $update_content
 * @property integer $is_change_special
 * @property integer $forced_updating
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class AppVersion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app_version';

    /**
     * @var array
     */
    protected $fillable = ['version_mark', 'version_coding', 'type', 'update_content', 'is_change_special', 'forced_updating', 'status', 'app_url','created_at', 'updated_at'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
