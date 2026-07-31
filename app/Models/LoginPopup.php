<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $content
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 */
class LoginPopup extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'login_popup';

    /**
     * @var array
     */
    protected $fillable = ['content', 'type', 'created_at', 'updated_at'];
}
