<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Cart;
use App\Models\User;

use Illuminate\Support\Facades\Response;

class NewCartAPIController extends Controller
{
    //

    public function index(Request $request){

    	
    	$api_token = $request->api_token;

    	$user_id = User::where('api_token',$api_token)->pluck('id');

    	if($user_id->isNotEmpty()){
    		$cartDetails = Cart::where('user_id',$user_id)
    					->with('food')
    					->with('restaurant')
    					->get();
    	}
    	else{
    		return response()->json([
                    'status'  => false,
                    'message' => 'No User found'
                ], 409);
    	}

    	

    	return response()->json($cartDetails);
    }
}

// fXLu7VeYgXDu82SkMxlLPG1mCAXc4EBIx6O5isgYVIKFQiHah0xiOHmzNsBv