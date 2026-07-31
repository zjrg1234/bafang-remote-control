<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiverTransmitterConfig extends Model
{
    use HasFactory;
    protected $table = 'receiver_transmitter_config';
    protected $fillable = [
        'receiver_id',
        'transmitter_id',
        'vehicle_id',
        'receiver_host_port',
        'transmitter_host_port'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
