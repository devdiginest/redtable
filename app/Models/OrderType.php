<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderType extends Model
{
    //

    public $table = 'order_types';

    public $fillable = [
        'name'
    ];

    protected $hidden    = ['created_at', 'updated_at'];
}
