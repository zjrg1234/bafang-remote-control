<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $content
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class Notice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notice';

    /**
     * @var array
     */
    protected $fillable = ['content', 'status', 'created_at', 'updated_at'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
