<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Models\Slide;
use App\Models\Meal;
use App\Models\Restaurant;
use App\Models\Category;

class HomeAPIController extends Controller
{
    //
    public function homesections(){

    	$slides         = Slide::get();
    	$meals 	        = Meal::get();
    	$restaurants    = Restaurant::get();
        $categories     = Category::select('id','name')->get();

        return $this->sendResponse([
                'slides'        => $slides,
                'meals'         => $meals,
                'restaurants'   => $restaurants,
                'categories'   => $categories
            ], 'Home data retrieved successfully');

    }
}
