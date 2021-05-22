<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Cart;
use App\Models\User;
use App\Models\DeliveryAddress;
use App\Models\DeliveryCharges;

use Illuminate\Support\Facades\Response;

class NewCartAPIController extends Controller
{
    //

    public function index(Request $request){

    	
    	$api_token = $request->api_token;

    	$cartTotal = array();
    	$cartDisCountTotal = array();

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

    	foreach ($cartDetails as $cartDetail) {
    		# code...
    		$deliveryAddress = DeliveryAddress::where('user_id',$user_id)->first();
    		$cartDetail->deliveryAddress = $deliveryAddress;

    		$cartDetail->itemTotalPrice = $cartDetail->food->price * $cartDetail->quantity;
    		$cartDetail->itemTotalDicountPrice = $cartDetail->food->discount_price * $cartDetail->quantity;

    		$cartTotal[] = $cartDetail->itemTotalPrice;
    		$cartDisCountTotal[] = $cartDetail->itemTotalDicountPrice;
    	}


    	$total = array_sum($cartTotal);
    	$disTotal = array_sum($cartDisCountTotal);

    	$billTotal = $total - $disTotal;

    	// Get Delivery Charge details

    	$area_id = $cartDetail->deliveryAddress->area_id;
    	$restaurant_id = $cartDetail->restaurant_id;

    	$delAddress = DeliveryCharges::where('area_id',$area_id)->where('restaurant_id',$restaurant_id)->first();

    	// return response()->json($delAddress->free_delivery_amount);

    	if($billTotal > $delAddress->free_delivery_amount){
    		$deliveryFee = 0;
    	}
    	else{
    		$deliveryFee = $delAddress->delivery_charge;
    	}

    	$totalBill = $billTotal + $deliveryFee;
    	

    	return response()->json([
    		'cartDetails' => $cartDetails,
    		'cartTotal'   => $total,
    		'cartDiscountTotal'   => $disTotal,
    		'deliveryFee'	=> $deliveryFee,
    		'totalBill'	=> $totalBill,
    	]);
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

    public function edit(Request $request){

    	$validator = Validator::make($request->all(), [
                'id'       	=> 'required|int|exists:carts,id',
                'quantity'  => 'required|int'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()
                ], 409);
            }

         try {

         		$currentTs = Carbon::now();
                $id = $request->input('id');

                $cart        = Cart::find($id);
                $cart->quantity         = $request->input('quantity');
                $cart->created_at       = $currentTs;
                $cart->updated_at       = $currentTs;
                
                $cart->save();

                return response()->json([
                    'status'  => true,
                    'message' => 'Cart Updated Successfully'
                ], 200);
            } catch (\Exception $e) {
            	echo $e;
                return response()->json([
                    'status'  => false,
                    'message' => 'Cart update failed'
                ], 409);
            }
    }

     public function delete($id) {

            $cart = Cart::find($id);

            if ($cart != null) {
                $cart->delete();

                return response()->json([
                    'status'  => true,
                    'message' => 'Cart items deleted'
                ], 200);
            }
            else{
                return response()->json([
                    'status'  => false,
                    'message' => 'No data found'
                ], 200);
            } 
        }
}

// fXLu7VeYgXDu82SkMxlLPG1mCAXc4EBIx6O5isgYVIKFQiHah0xiOHmzNsBv