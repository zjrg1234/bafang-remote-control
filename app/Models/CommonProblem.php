<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property integer $sort
 * @property integer $status
 * @property string $detail
 * @property string $created_at
 * @property string $updated_at
 */
class CommonProblem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'common_problem';

    /**
     * @var array
     */
    protected $fillable = ['name', 'sort', 'status', 'detail', 'created_at', 'updated_at'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
