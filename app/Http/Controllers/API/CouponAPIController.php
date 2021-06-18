<?php

namespace App\Http\Controllers\API;


use App\Criteria\Coupons\ValidCriteria;
use App\Models\Coupon;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;

use Illuminate\Support\Facades\Validator;

use App\Models\DeliveryCharges;
use App\Models\Restaurant;

/**
 * Class CouponController
 * @package App\Http\Controllers\API
 */

class CouponAPIController extends Controller
{
    /** @var  CouponRepository */
    private $couponRepository;

    public function __construct(CouponRepository $couponRepo)
    {
        $this->couponRepository = $couponRepo;
    }

    /**
     * Display a listing of the Coupon.
     * GET|HEAD /coupons
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->couponRepository->pushCriteria(new RequestCriteria($request));
            $this->couponRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->couponRepository->pushCriteria(new ValidCriteria());
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $coupons = $this->couponRepository->all();

        return $this->sendResponse($coupons->toArray(), 'Coupons retrieved successfully');
    }

    /**
     * Display the specified Coupon.
     * GET|HEAD /coupons/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Coupon $coupon */
        if (!empty($this->couponRepository)) {
            $coupon = $this->couponRepository->findWithoutFail($id);
        }

        if (empty($coupon)) {
            return $this->sendError('Coupon not found');
        }

        return $this->sendResponse($coupon->toArray(), 'Coupon retrieved successfully');
    }

    public function apply_coupon(Request $request){

        $validator = Validator::make($request->all(), [
                'restaurant_id'    => 'required|int|exists:restaurants,id',
                'area_id'          => 'required|int|exists:areas,id',
                'item_total'       => 'required|int',
                'coupon_code'      => 'required|string|exists:coupons,code'
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()
                ], 409);
            }
        try {
                $restaurantId   = $request->input('restaurant_id');
                $areaId         = $request->input('area_id');
                $itemTotal      = $request->input('item_total');
                $couponCode     = $request->input('coupon_code');

                $deliveryCharge = DeliveryCharges::where('restaurant_id',$restaurantId)
                                    ->where('area_id',$areaId)->first();
                $couponDetails  = Coupon::where('code',$couponCode)->first();
                $discountType    = $couponDetails->discount_type;
                $discountValue   = $couponDetails->discount;

                $restaurantTax = Restaurant::where('id',$restaurantId)->select('default_tax')->first();

               if($discountType == 'percent'){
                    $discountRate = $itemTotal * $discountValue;
                    $discountRate = $discountRate/100;
               }
               elseif ($discountType == 'fixed') {
                   // code...
                    $minusRate = $itemTotal - $discountValue;
               }

               $minusRate = $itemTotal - $discountRate;

                if($minusRate > $deliveryCharge->free_delivery_amount){
                    $deliveryFee = 0;
                }
                if($minusRate < $deliveryCharge->free_delivery_amount){
                    $deliveryFee = $deliveryCharge->delivery_charge;
                }

                // $Bill = $minusRate + $deliveryFee;

                $taxRate = $minusRate * $restaurantTax->default_tax;
                $taxRate = $taxRate/100;

                // $totalBill = $minusRate + $taxRate + $deliveryFee;

                $totalBill = $minusRate + $taxRate;

                return $this->sendResponse(
                    [
                    'cartTotal'     => $itemTotal,
                    'cartDiscount'  => number_format((float)$discountRate, 2, '.', ''),
                    'deliveryFee'   => $deliveryFee,
                    'taxPercentage' => $restaurantTax->default_tax,
                    'tax'           => number_format((float)$taxRate, 2, '.', ''),
                    'totalBill'     => number_format((float)$totalBill, 2, '.', ''),
                ], 'Cart Updated successfully');

                } catch (\Exception $e) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Failed to update cart'
                    ], 409);
                }
    }
}
