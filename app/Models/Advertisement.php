<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $title
 * @property string $image
 * @property integer $status
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 */
class Advertisement extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'advertisement';

    /**
     * @var array
     */
    protected $fillable = ['title', 'image', 'status', 'type', 'created_at', 'updated_at'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
