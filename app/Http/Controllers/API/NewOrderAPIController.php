<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use App\Notifications\NewOrder;


use App\Repositories\FoodOrderRepository;
use App\Repositories\CartRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;

use App\Models\Order;
use App\Models\User;

class NewOrderAPIController extends Controller
{
    //

    /** @var  OrderRepository */
    private $orderRepository;
    /** @var  FoodOrderRepository */
    private $foodOrderRepository;
    /** @var  CartRepository */
    private $cartRepository;
    /** @var  UserRepository */
    private $userRepository;
    /** @var  PaymentRepository */
    private $paymentRepository;
    /** @var  NotificationRepository */
    private $notificationRepository;

    public function __construct(OrderRepository $orderRepo, FoodOrderRepository $foodOrderRepository, CartRepository $cartRepo, PaymentRepository $paymentRepo, NotificationRepository $notificationRepo, UserRepository $userRepository)
    {
        
        $this->orderRepository = $orderRepo;
        $this->foodOrderRepository = $foodOrderRepository;
        $this->cartRepository = $cartRepo;
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepo;
        $this->notificationRepository = $notificationRepo;
    }

    public function myOrders(Request $request){

    	$api_token = $request->api_token;

    	$user_id = User::where('api_token',$api_token)->pluck('id');

    	if($user_id->isNotEmpty()){

    		$orderDetails = Order::where('user_id',$user_id)
    						->with('orderStatus')
    						->with('orderTypes')
    						->with('foodOrders.food')
    						->get();
    	}
    	else{
    		return $this->sendError('no user found');
    	}

    	
        return $this->sendResponse($orderDetails,'Orders retrieved successfully');
    	// return response()->json($orderDetails);

    }

    public function show($id){


		$orderDetails = Order::where('id',$id)
						->with('orderStatus')
						->with('orderTypes')
						->with('foodOrders.food')
						->with('restaurant')
						->get();

        if($orderDetails->isEmpty()){
            return $this->sendError('no orders found');
        }
        else{
            return $this->sendResponse($orderDetails,'Orders retrieved successfully');
        }

    }

    public function store(Request $request){
        $payment = $request->only('payment');
        if (isset($payment['payment']) && $payment['payment']['method']) {
            if ($payment['payment']['method'] == "Credit Card (Stripe Gateway)") {
                return $this->stripPayment($request);
            } else {
                return $this->cashPayment($request);

            }
        }
    }

    private function stripPayment(Request $request)
    {
        $input = $request->all();
        $amount = 0;
        try {
            $user = $this->userRepository->findWithoutFail($input['user_id']);
            if (empty($user)) {
                return $this->sendError('User not found');
            }
            $stripeToken = Token::create(array(
                "card" => array(
                    "number" => $input['stripe_number'],
                    "exp_month" => $input['stripe_exp_month'],
                    "exp_year" => $input['stripe_exp_year'],
                    "cvc" => $input['stripe_cvc'],
                    "name" => $user->name,
                )
            ));
            if ($stripeToken->created > 0) {
                if (empty($input['delivery_address_id'])) {
                    $order = $this->orderRepository->create(
                        $request->only('user_id', 'order_status_id', 'tax', 'hint')
                    );
                } else {
                    $order = $this->orderRepository->create(
                        $request->only('user_id', 'order_status_id', 'tax', 'delivery_address_id', 'delivery_fee', 'hint')
                    );
                }
                foreach ($input['foods'] as $foodOrder) {
                    $foodOrder['order_id'] = $order->id;
                    $foodOrder['restaurant_id'] = $input['restaurant_id'];
                    $amount += $foodOrder['price'] * $foodOrder['quantity'];
                    $this->foodOrderRepository->create($foodOrder);
                }
                $amount += $order->delivery_fee;
                $amountWithTax = $amount + ($amount * $order->tax / 100);
                $charge = $user->charge((int)($amountWithTax * 100), ['source' => $stripeToken]);
                $payment = $this->paymentRepository->create([
                    "user_id" => $input['user_id'],
                    "description" => trans("lang.payment_order_done"),
                    "price" => $amountWithTax,
                    "status" => $charge->status, // $charge->status
                    "method" => $input['payment']['method'],
                ]);
                $this->orderRepository->update(['payment_id' => $payment->id], $order->id);

                $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

                Notification::send($order->foodOrders[0]->food->restaurant->users, new NewOrder($order));
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    private function cashPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|int|exists:users,id',
            'order_status_id'   => 'required|int',
            'order_type'        => 'required|int',
            'tax'               => 'nullable|int',
            'delivery_fee'      => 'nullable|int',
            'hint'              => 'nullable|string',
            'active'            => 'required|int',
            'driver_id'         => 'nullable|int',
            'delivery_address_id'        => 'int|exists:delivery_addresses,id',
            'restaurant_id'        => 'int|exists:restaurants,id',
            'delivery_note'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input = $request->all();
        $amount = 0;
        $admins = User::whereHas(
                                'roles', function($q){
                                    $q->where('name', 'admin');
                                }
                            )->get();
            // return($admins);
        try {
            $currentTs = Carbon::now();

            $order                      = new Order;
            $order->user_id             = $request->input('user_id');
            $order->order_status_id     = $request->input('order_status_id');
            $order->order_type          = $request->input('order_type');
            $order->tax                 = $request->input('tax');
            $order->delivery_fee        = $request->input('delivery_fee');
            $order->hint                = $request->input('hint');
            $order->active              = $request->input('active');
            $order->delivery_address_id = $request->input('delivery_address_id');
            $order->restaurant_id       = $request->input('restaurant_id');
            $order->delivery_note       = $request->input('delivery_note');
            $order->created_at          = $currentTs;
            $order->updated_at          = $currentTs;
            
            $order->save();

            //return($input['foods']);

            
            foreach ($input['foods'] as $foodOrder) {
                $foodOrder['order_id'] = $order->id;
                $amount += $foodOrder['price'] * $foodOrder['quantity'];
                $this->foodOrderRepository->create($foodOrder);
            }
            $amount += $order->delivery_fee;
            $amountWithTax = $amount + ($amount * $order->tax / 100);
            $payment = $this->paymentRepository->create([
                "user_id" => $input['user_id'],
                "description" => trans("lang.payment_order_waiting"),
                "price" => $amountWithTax,
                "status" => 'Waiting for Client',
                "method" => $input['payment']['method'],
            ]);

            $this->orderRepository->update(['payment_id' => $payment->id], $order->id);

            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);


            // Notification::send($admins, new NewOrder($order));

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    public function cancel_order($oid){
        
        $order = Order::where('id',$oid)->where('active',1)->first();
        if($order != null){
            if($order->order_status_id = 1){
                $orderStatus = Order::find($oid);
                $orderStatus->active = 0;
                $orderStatus->save();
            }
            else{
                return $this->sendError('Not able to cancel the order');
            }
        }
        else{
            return $this->sendError('Order not found');
        }
        return $this->sendResponse($order->toArray(), 'Order Cancelled');
    }
}
