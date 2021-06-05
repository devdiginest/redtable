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
                
                    $currentTs  = Carbon::now()->toDateString();

                    $courseReview                   = new CourseReview;
                    $courseReview->student          = $request->input('student');
                    $courseReview->course           = $request->input('course');
                    $courseReview->rating           = $request->input('rating');
                    $courseReview->review           = $request->input('review');
                    $courseReview->date             = $currentTs;
                    $courseReview->save();

                    return response()->json([
                        'status'  => true,
                        'message' => 'Successfully added review'
                    ], 201);

                } catch (\Exception $e) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Review adding failed'
                    ], 409);
                }
    }
}
