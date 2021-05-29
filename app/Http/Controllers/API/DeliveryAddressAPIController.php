<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;
use App\Repositories\DeliveryAddressRepository;
use Flash;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

use Illuminate\Support\Facades\Validator;

/**
 * Class DeliveryAddressController
 * @package App\Http\Controllers\API
 */
class DeliveryAddressAPIController extends Controller
{
    /** @var  DeliveryAddressRepository */
    private $deliveryAddressRepository;

    public function __construct(DeliveryAddressRepository $deliveryAddressRepo)
    {
        $this->deliveryAddressRepository = $deliveryAddressRepo;
    }

    /**
     * Display a listing of the DeliveryAddress.
     * GET|HEAD /deliveryAddresses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->deliveryAddressRepository->pushCriteria(new RequestCriteria($request));
            $this->deliveryAddressRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $deliveryAddresses = $this->deliveryAddressRepository->all();

        return $this->sendResponse($deliveryAddresses->toArray(), 'Delivery Addresses retrieved successfully');
    }

    /**
     * Display the specified DeliveryAddress.
     * GET|HEAD /deliveryAddresses/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var DeliveryAddress $deliveryAddress */
        if (!empty($this->deliveryAddressRepository)) {
            $deliveryAddress = $this->deliveryAddressRepository->findWithoutFail($id);
        }

        if (empty($deliveryAddress)) {
            return $this->sendError('Delivery Address not found');
        }

        return $this->sendResponse($deliveryAddress->toArray(), 'Delivery Address retrieved successfully');
    }

    /**
     * Store a newly created DeliveryAddress in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $uniqueInput = $request->only("address");
        $otherInput = $request->except("address");
        try {
            $deliveryAddress = $this->deliveryAddressRepository->updateOrCreate($uniqueInput, $otherInput);

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($deliveryAddress->toArray(), __('lang.saved_successfully', ['operator' => __('lang.delivery_address')]));
    }

    /**
     * Update the specified DeliveryAddress in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $deliveryAddress = $this->deliveryAddressRepository->findWithoutFail($id);

        if (empty($deliveryAddress)) {
            return $this->sendError('Delivery Address not found');
        }
        $input = $request->all();
        if ($input['is_default'] == true){
            $this->deliveryAddressRepository->initIsDefault($input['user_id']);
        }
        try {
            $deliveryAddress = $this->deliveryAddressRepository->update($input, $id);
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($deliveryAddress->toArray(), __('lang.updated_successfully', ['operator' => __('lang.delivery_address')]));

    }

    /**
     * Remove the specified Address from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $address = $this->deliveryAddressRepository->findWithoutFail($id);

        if (empty($address)) {
            return $this->sendError('Delivery Address Not found');

        }

        $this->deliveryAddressRepository->delete($id);

        return $this->sendResponse($address, __('lang.deleted_successfully',['operator' => __('lang.delivery_address')]));

    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
                'description'       => 'required|string|max:100',
                'address'           => 'required|string|max:150',
                'default'           => 'required|numeric|max:1',
                'user_id'           => 'required|numeric|exists:users,id',
                'area_id'           => 'required|numeric|exists:areas,id',
            ]);


            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            $description = $request->input('description');
            $address = $request->input('address');
            $default = $request->input('default');
            $user_id = $request->input('user_id');
            $area_id = $request->input('area_id');

            try {
                
                    
                    $deliveryAddress                = new DeliveryAddress;
                    $deliveryAddress->description   = $description;
                    $deliveryAddress->address       = $address;
                    $deliveryAddress->is_default    = $default;
                    $deliveryAddress->user_id       = $user_id;
                    $deliveryAddress->area_id       = $area_id;
                    $deliveryAddress->save();

                    return $this->sendResponse($deliveryAddress,'Successfully added new address');
                
               
            } catch (\Exception $e) {
                return $this->sendError('failed to add address');
            }
    }

    public function edit(Request $request){
        $validator = Validator::make($request->all(), [
                'id'                => 'required|int|exists:delivery_addresses,id',
                'description'       => 'required|string|max:100',
                'address'           => 'required|string|max:150',
                'default'           => 'required|numeric|max:1',
                'user_id'           => 'required|numeric|exists:users,id',
                'area_id'           => 'required|numeric|exists:areas,id',
            ]);


            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            $id = $request->input('id');
            $description = $request->input('description');
            $address = $request->input('address');
            $default = $request->input('default');
            $user_id = $request->input('user_id');
            $area_id = $request->input('area_id');

            try {
                
                    
                    $deliveryAddress                = DeliveryAddress::find($id);
                    $deliveryAddress->description   = $description;
                    $deliveryAddress->address       = $address;
                    $deliveryAddress->is_default    = $default;
                    $deliveryAddress->user_id       = $user_id;
                    $deliveryAddress->area_id       = $area_id;
                    $deliveryAddress->save();
                    

                    return $this->sendResponse($deliveryAddress,'Address edited successfully');
                
               
            } catch (\Exception $e) {
                return $this->sendError('failed to edit address');
            }
    }
}