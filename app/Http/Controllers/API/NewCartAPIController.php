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
            if($cartDetails->isEmpty()){
                return $this->sendResponse($cartDetails,'Cart is Empty');
            }
    	}
    	else{
            return $this->sendError('No User found');
    	}

    	foreach ($cartDetails as $cartDetail) {
    		# code...
    		$deliveryAddress = DeliveryAddress::where('user_id',$user_id)->where('is_default',1)->first();
    		$cartDetail->deliveryAddress = $deliveryAddress;

    		$cartDetail->itemTotalPrice = $cartDetail->food->price * $cartDetail->quantity;
    		$cartDetail->itemTotalDicountPrice = $cartDetail->food->discount_price * $cartDetail->quantity;
            $cartDetail->food_id = $cartDetail->food->id;

    		$cartTotal[] = $cartDetail->itemTotalPrice;
    		$cartDisCountTotal[] = $cartDetail->itemTotalDicountPrice;

            $restTax = $cartDetail->restaurant->default_tax;
    	}


    	$total = array_sum($cartTotal);
    	$disTotal = array_sum($cartDisCountTotal);

    	$discount = $total - $disTotal;

    	// Get Delivery Charge details

    	$area_id = $cartDetail->deliveryAddress->area_id;
    	$restaurant_id = $cartDetail->restaurant_id;

    	$delAddress = DeliveryCharges::where('area_id',$area_id)->where('restaurant_id',$restaurant_id)->first();

        if($delAddress == null){
            return $this->sendError('No delivery to this addresss. Please check another address');
        }

    	// return response()->json($delAddress->free_delivery_amount);

    	if($disTotal > $delAddress->free_delivery_amount){
    		$deliveryFee = 0;
    	}
    	else{
    		$deliveryFee = $delAddress->delivery_charge;
    	}

    	// $Bill = $disTotal + $deliveryFee;

        $taxRate = $disTotal * $restTax;
        $taxRate = $taxRate/100;

        // $totalBill = $disTotal + $taxRate + $deliveryFee;
        $totalBill = $disTotal + $taxRate;
        

    	
        return $this->sendResponse(
            [
            'cartDetails'   => $cartDetails,
            'cartTotal'     => $total,
            'cartDiscount'  => number_format((float)$discount, 2, '.', ''),
            // 'deliveryFee'   => (int)$deliveryFee,
            'taxPercentage' => $restTax,
            'tax'           => number_format((float)$taxRate, 2, '.', ''),
            'totalBill'     => number_format((float)$totalBill, 2, '.', ''),
        ], 'Cart details retrieved successfully');
    }

    public function create(Request $request){

    	$validator = Validator::make($request->all(), [
                'food_id'       	=> 'required|int|exists:foods,id',
                'restaurant_id'     => 'required|int|exists:restaurants,id',
                'user_id'    		=> 'required|string|max:100',
                'quantity'    		=> 'required|int'
            ]);

            if ($validator->fails()) {

                return $this->sendError($validator->errors());
                
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

                return $this->sendResponse($cart,'Successfully added to cart');

                
            } catch (\Exception $e) {
                return $this->sendError('Add to cart failed');
            }
    }

    public function edit(Request $request){

    	$validator = Validator::make($request->all(), [
                'id'       	=> 'required|int|exists:carts,id',
                'quantity'  => 'required|int'
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

         try {

         		$currentTs = Carbon::now();
                $id = $request->input('id');

                $cart        = Cart::find($id);
                $cart->quantity         = $request->input('quantity');
                $cart->created_at       = $currentTs;
                $cart->updated_at       = $currentTs;
                
                $cart->save();

                return $this->sendResponse($cart,'Cart Updated Successfully');

            } catch (\Exception $e) {
            	echo $e;
                return $this->sendError('failed to update cart');
            }
    }

    // Clear Cart

    public function clearcart(Request $request){
        $validator = Validator::make($request->all(), [
                'user_id'           => 'required|int|exists:users,id'
            ]);

            if ($validator->fails()) {

                return $this->sendError($validator->errors());
                
            }

            try {
                $id = $request->input('user_id');

                $clearCart = Cart::where('user_id', $id)->delete();

                return $this->sendResponse($clearCart,'Cart Cleared');

            } catch (\Exception $e) {
                echo $e;
                return $this->sendError('failed to clear cart');
            }

    }

     public function delete($id) {

            $cart = Cart::find($id);

            if ($cart != null) {
                $cart->delete();

                return $this->sendResponse($cart,'Cart items deleted');
            }
            else{
                return $this->sendError('no data found');
            } 
        }
}
