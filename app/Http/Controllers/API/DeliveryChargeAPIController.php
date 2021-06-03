<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\DeliveryCharges;
use App\Repositories\DeliveryChargeRepository;
use Flash;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class DeliveryChargeController
 * @package App\Http\Controllers\API
 * * Author:Twixt Technologies
 */
class DeliveryChargeAPIController extends Controller
{
    /** @var  DeliveryChargeRepository */
    private $deliveryChargeRepository;

    public function __construct(DeliveryChargeRepository $deliveryChargeRepo)
    {
        $this->deliveryChargeRepository = $deliveryChargeRepo;
    }

    /**
     * Display a listing of the DeliveryCharge.
     * GET|HEAD /deliveryChargees
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->deliveryChargeRepository->pushCriteria(new RequestCriteria($request));
            $this->deliveryChargeRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $deliveryChargees = $this->deliveryChargeRepository->all();

        return $this->sendResponse($deliveryChargees->toArray(), 'Delivery Addresses retrieved successfully');
        
    }

    public function search($areaId,$restaurantId)
    {
        
        if (!empty($this->deliveryChargeRepository)) {     
            
            $deliveryCharges = DeliveryCharges::where('area_id',$areaId)->where('restaurant_id',$restaurantId)->first();
        }

        if (empty($deliveryCharges)) {
            return $this->sendError('not found');
        }

        return $this->sendResponse($deliveryCharges->toArray(), 'Delivery Addresses retrieved successfully');
    }

    public function getarea($restaurantid){
        $deliveryAreas = DeliveryCharges::select('area_id')->where('restaurant_id',$restaurantid)->with('area')->get();

        $newarray = array();

        foreach ($deliveryAreas as $deliveryArea) {
            $deliveryArea->label = $deliveryArea->area->name;
            $deliveryArea->value = $deliveryArea->area->id;
        }

        if($deliveryAreas->isEmpty()){
            return $this->sendError('not areas found');
        }

        // $deliveryAreas = $deliveryAreas->with('area');

        return $this->sendResponse($deliveryAreas, 'Delivery Areas retrieved successfully');

    }
}