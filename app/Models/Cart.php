<?php
/**
 * File name: Cart.php
 * Last modified: 2021.05.20 at 07:34:52
 * Author: Diginest Solutions - https://diginestsolutions.com
 * Copyright (c) 2021
 */

namespace App\Models;

use Eloquent as Model;

/**
 * Class Cart
 * @package App\Models
 *
 * @property \App\Models\Food food
 * @property \App\Models\User user
 * @property \Illuminate\Database\Eloquent\Collection extras
 * @property integer food_id
 * @property integer user_id
 * @property integer quantity
 */
class Cart extends Model
{

    public $table = 'carts';
    


    public $fillable = [
        'food_id',
        'user_id',
        'quantity',
        'restaurant_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'food_id' => 'integer',
        'user_id' => 'integer',
        'quantity' => 'integer',
        'restaurant_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'food_id' => 'required|exists:foods,id',
        'user_id' => 'required|exists:users,id'
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields'
    ];

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class,setting('custom_field_models',[]));
        if (!$hasCustomField){
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields','custom_fields.id','=','custom_field_values.custom_field_id')
            ->where('custom_fields.in_table','=',true)
            ->get()->toArray();

        return convertToAssoc($array,'name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function food()
    {
        return $this->belongsTo(\App\Models\Food::class, 'food_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function restaurant()
    {
        //return $this->belongsTo(\App\Models\RestaurantFood::class, 'food_id', 'food_id');
        return $this->belongsTo(\App\Models\Restaurant::class, 'restaurant_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function extras()
    {
        return $this->belongsToMany(\App\Models\Extra::class, 'cart_extras');
    }
}
