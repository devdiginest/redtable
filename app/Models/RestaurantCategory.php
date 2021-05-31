<?php
/**
 * File name: RestaurantCategory.php
 * Last modified: 2021.05.11 at 05:57:09
 * Author: Diginest - http://diginetsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Models;

use Eloquent as Model;

/**

 */
class RestaurantCategory extends Model
{

    public $table = 'restaurant_categories';
    
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function restaurant()
    {
        return $this->belongsTo(\App\Models\Restaurant::class, 'restaurant_id', 'id');
    }
}
