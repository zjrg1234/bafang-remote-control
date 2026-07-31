<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $uid
 * @property integer $agents_id
 * @property string $user_name
 * @property string $phone
 * @property string $Content
 * @property string $image
 * @property integer $type
 * @property string $time
 * @property string $created_at
 * @property string $updated_at
 */
class FeedBack extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feed_back';

    /**
     * @var array
     */
    protected $fillable = ['uid', 'agents_id', 'user_name', 'phone', 'Content', 'image', 'type', 'time','remark', 'created_at', 'updated_at'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
