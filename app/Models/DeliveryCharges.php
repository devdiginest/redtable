<?php
/**
 * File name: Deliverycharges.php
 * Last modified: 2021.05.20 at 06:53:42
 * Author: Diginest Solutions - http://diginestsolutions.com
 * Copyright (c) 2021
 *
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCharges extends Model
{
    //
	  public $table = 'delivery_charges';

	  public $timestamps = false;

	  public $fillable = [
	    'id',
	    'area_id',
	    'restaurant_id',
	    'free_delivery_amount',
	    'delivery_charge'
	  ];

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

	 
	  public function area(){

	    return $this->belongsTo(\App\Models\Area::class,'area_id','id');

	  }
	  public function restaurant(){

	    return $this->belongsTo(\App\Models\Restaurant::class,'restaurant_id','id');

	  }
}
