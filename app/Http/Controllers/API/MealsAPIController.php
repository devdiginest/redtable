<?php
/**
 * File name: MealsAPIController.php
 * Last modified: 2021.04.08 at 09:42
 * Author: DiginestSolutions - https://diginestsolutions.com
 * Copyright (c) 2020
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Prettus\Validator\Exceptions\ValidatorException;

use Illuminate\Support\Facades\DB;

use App\Models\Meal;
use App\Models\Restaurant;
use App\Models\RestaurantReview;
use App\Models\Category;

class MealsAPIController extends Controller
{
    public function index(){

        $getmeals = Meal::get();

        return $this->sendResponse($getmeals, 'Meals retrieved successfully');
    }

    public function getrestaurants($mid){

    	$url = url('/storage/app/public');

    	$restaurants = DB::table('meal_foods')
    					->join('restaurant_foods','meal_foods.food_id','=','restaurant_foods.food_id')
    					->join('restaurants','restaurant_foods.restaurant_id','=','restaurants.id')
    					->select('restaurant_foods.restaurant_id','restaurants.name','restaurants.address')
    					->where('meal_foods.meal_id','=',$mid)->distinct()->get();
    	foreach ($restaurants as $restaurant) {
                $restaurant->ratings = RestaurantReview::where('restaurant_id', $restaurant->restaurant_id)->avg('rate');
                $restaurant->ratingscount = RestaurantReview::where('restaurant_id', $restaurant->restaurant_id)->where('rate', '<>', '')->count();
                $restaurant->cuisines	= DB::table('restaurant_cuisines')
                							->join('cuisines','restaurant_cuisines.cuisine_id','=','cuisines.id')
                							->where('restaurant_cuisines.restaurant_id', $restaurant->restaurant_id)
                							->select('cuisines.name')->get();
                $media = DB::table('media')->where('model_id',$restaurant->restaurant_id)->where('model_type','=','App\Models\Restaurant')->select('id','file_name')->first();
                
                $restaurant->media_url = $url.'/'.$media->id.'/'.$media->file_name;
            }

    	// $restArray = array();

    	// foreach ($restaurants as $restaurant) {
    	// 	$rests = Restaurant::where('id',$restaurant->restaurant_id)->get();
    	// 	$restArray[] = $rests;
    	// }

    	return response()->json($restaurants);


    }
}
