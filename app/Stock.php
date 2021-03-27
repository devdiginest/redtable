<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $dates = [
	    'created_at',
	    'updated_at',
	    // your other new column
	];
}
