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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function meals()
    {
        return $this->belongsToMany(\App\Models\Food::class, 'meal_foods');
    }
}
