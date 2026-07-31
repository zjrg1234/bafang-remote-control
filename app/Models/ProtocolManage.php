<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 */
class ProtocolManage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'protocol_manage';

    /**
     * @var array
     */
    protected $fillable = ['type', 'name', 'content', 'created_at', 'updated_at'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
