<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarZone extends Model
{
    use HasFactory;
    protected $table = 'war_zone';

    protected $fillable = [
        'agent_id',
        'name',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
