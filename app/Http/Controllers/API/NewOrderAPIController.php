<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\User;

class NewOrderAPIController extends Controller
{
    //
    public function myOrders(Request $request){

    	$api_token = $request->api_token;

    	$user_id = User::where('api_token',$api_token)->pluck('id');

    	if($user_id->isNotEmpty()){
    		// $orderDetails = DB::table('orders')
    		// 				->join('food_orders','orders.id','=','food_orders.order_id')
    		// 				->join('foods','food_orders.food_id','=','foods.id')
    		// 				->join('order_statuses','orders.order_status_id','=','order_statuses.id')
    		// 				->select('orders.id','foods.name','food_orders.quantity')
    		// 				->where('user_id',$user_id)
    		// 				->get();

    		$orderDetails = Order::where('user_id',$user_id)
    						->with('orderStatus')
    						->with('orderTypes')
    						->with('foodOrders.food')
    						->get();
    	}
    	else{
    		return response()->json([
                    'status'  => false,
                    'message' => 'No User found'
                ], 409);
    	}

    	

    	return response()->json($orderDetails);

    }
}
