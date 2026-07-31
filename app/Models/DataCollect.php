<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property float $total_sale
 * @property integer $total_make
 * @property integer $total_payment
 * @property float $total_refund
 * @property string $created_at
 * @property string $updated_at
 */
class DataCollect extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'data_collect';

    /**
     * @var array
     */
    protected $fillable = ['total_sale', 'total_make', 'total_payment', 'total_refund', 'created_at', 'updated_at'];
}
