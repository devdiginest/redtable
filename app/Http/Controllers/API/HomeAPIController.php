<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Models\Slide;
use App\Models\Meal;
use App\Models\Restaurant;

class HomeAPIController extends Controller
{
    //
    public function homesections(){

    	$slides = Slide::get();
    	$meals 	= Meal::get();
    	$restaurants 	= Restaurant::get();

    	return response()->json([
                'slides'        => $slides,
                'meals'         => $meals,
                'restaurants'   => $restaurants
            ], 200);
    }
}
