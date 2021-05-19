<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    public function create(Request $request){

    	$validator = Validator::make($request->all(), [
                'food_id'       	=> 'required|int|exists:foods,id',
                'restaurant_id'     => 'required|int|exists:restaurants,id',
                'user_id'    		=> 'required|string|max:100',
                'quantity'    		=> 'required|int'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()
                ], 409);
            }

         try {

         		$currentTs = Carbon::now();

                $cart                   = new Cart;
                $cart->food_id          = $request->input('food_id');
                $cart->restaurant_id    = $request->input('restaurant_id');
                $cart->user_id          = $request->input('user_id');
                $cart->quantity         = $request->input('quantity');
                $cart->created_at       = $currentTs;
                $cart->updated_at       = $currentTs;
                
                $cart->save();

                return response()->json([
                    'status'  => true,
                    'message' => 'Successfully added to cart'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'add to cart failed'
                ], 409);
            }
    }
}

// fXLu7VeYgXDu82SkMxlLPG1mCAXc4EBIx6O5isgYVIKFQiHah0xiOHmzNsBv