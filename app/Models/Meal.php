<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    //
    public $table = 'meals';

    public $fillable = [
        'name',
        'description'
    ];

    protected $hidden    = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function food()
    {
        return $this->belongsToMany(\App\Models\Food::class, 'meal_foods');
    }
}
