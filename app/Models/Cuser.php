<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuser extends Model
{
    use HasFactory;
    protected $table = 'cuser';
    protected $fillable = [
        'username',
        'password',
        'phone_number',
        'special_area',
        'head_shot',
        'is_real_name',
        'real_name',
        'is_cancel',
        'register_time',
        'login_ip',
        'last_online_time',
        'session_key',
        'is_locked',
        'nick_name',
        'special_area_name',
        'is_delete',
        'show_id',
        'is_screenshot'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
