<?php
/**
 * File name: Cart.php
 * Last modified: 2021.05.20 at 07:34:52
 * Author: Diginest Solutions - https://diginestsolutions.com
 * Copyright (c) 2021
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    //

    public $table = 'areas';

	public $fillable = [
		'name',
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
}
