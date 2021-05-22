<?php

/**
 * File name: DeliveryChargeController.php
 * Last modified: 2021.05.20 at 07:23:19
 * Author: Diginest Solutions - http://diginestsolutions.com
 * Copyright (c) 2021
 *
 */

namespace App\Http\Controllers;

use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Models\DeliveryCharges;
use App\DataTables\DeliveryChargeDataTable;
use App\Repositories\DeliveryChargeRepository;
use App\Repositories\CustomFieldRepository;
use App\Models\Area;
use App\Models\Restaurant;
use DB;
use Exception;

class DeliveryChargeController extends Controller
{
    //

    /** @var  DeliveryChargeRepository */
    private $DeliveryChargeRepository;
    
    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;
    
    public function __construct(DeliveryChargeRepository $DeliveryChargeRepo, CustomFieldRepository $customFieldRepo)
    {
        parent::__construct();
        $this->DeliveryChargeRepository = $DeliveryChargeRepo;
        $this->customFieldRepository = $customFieldRepo;
    }
    // public function index()
    // {
    //     // dd("Delivery Charges");
    //     $charges = DeliveryCharges::all();
    //     return view('deliverycharge.index',compact('charges'));
    // }
    /**
     * Display a listing of the DeliveryCharges.
     *
     * @param DeliveryChargeDataTable $DeliveryChargeDataTable
     * @return Response
     */
    public function index(DeliveryChargeDataTable $DeliveryChargeDataTable)
    {
        return $DeliveryChargeDataTable->render('deliverycharge.index');
    }
    /**
     * Show the form for creating a new RestaurantReview.
     *
     * @return Response
     */
    public function create()
    {
        $charge = null;
        $areas = Area::pluck('name', 'id')->toArray();
        $restaurants = Restaurant::pluck('name', 'id')->toArray();
        $hasCustomField = in_array($this->DeliveryChargeRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->DeliveryChargeRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('deliverycharge.create',compact('charge','areas','restaurants'))->with("customFields", isset($html) ? $html : false);
    
    }

    /**
     * Store a newly created RestaurantReview in storage.
     *
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'area_id' => 'required',
            'restaurant_id' => 'required',
            'free_delivery_amount' => 'required|numeric',
            'delivery_charge' => 'required|numeric'

        ],[
            
            'area_id.required' => 'Please select area',
            'restaurant_id.required' => 'Please select restaurant',
            'free_delivery_amount.required' => 'Please enter free delivery amount',
            'free_delivery_amount.numeric' => 'Free delivery amount should be a number',
            'delivery_charge.required' => 'Please enter delivery charge',
            'delivery_charge.numeric' => 'Delivery charge should be a number'
        ]);
        try {
            $areas = DeliveryCharges::where('restaurant_id',$request->restaurant_id)->where('area_id',$request->area_id)->count();
            if($areas==0){
                $charge = new DeliveryCharges();
                $charge->area_id = $request->area_id;
                $charge->restaurant_id = $request->restaurant_id;
                $charge->free_delivery_amount = $request->free_delivery_amount;
                $charge->delivery_charge = $request->delivery_charge;
                $charge->save();
                Flash::success(__('lang.saved_successfully', ['operator' => __('lang.delivery_charge')]));
                return redirect(url('Delivery-Charges'));
            }
            else{
                Flash::error('This restaurant have this place already,please try another !!');
                return back();
            }
            
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
            return back();
        }
    }

    /**
     * Display the specified Restaurant Charge.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
    
    }

    /**
     * Show the form for editing the specified Restaurant Charge.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function edit($id)
    {
        $charge = DeliveryCharges::find($id);
        $areas = Area::pluck('name', 'id')->toArray();
        $restaurants = Restaurant::pluck('name', 'id')->toArray();
        $customFieldsValues = $charge->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->DeliveryChargeRepository->model());
        $hasCustomField = in_array($this->DeliveryChargeRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('deliverycharge.edit',compact('charge','areas','restaurants'))->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Restaurant Charge in storage.
     *
     */
    public function update($id, Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'area_id' => 'required',
            'restaurant_id' => 'required',
            'free_delivery_amount' => 'required|numeric',
            'delivery_charge' => 'required|numeric'

        ],[
            
            'area_id.required' => 'Please select area',
            'restaurant_id.required' => 'Please select restaurant',
            'free_delivery_amount.required' => 'Please enter free delivery amount',
            'free_delivery_amount.numeric' => 'Free delivery amount should be a number',
            'delivery_charge.required' => 'Please enter delivery charge',
            'delivery_charge.numeric' => 'Delivery charge should be a number'
        ]);
            
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->DeliveryChargeRepository->model());
        try {
                $charge = DeliveryCharges::find($id);
                foreach (getCustomFieldsValues($customFields, $request) as $value) {
                    $charge->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
                }
                // $charge = DeliveryCharges::updateOrCreate(
                //     ['area_id' => $request->area_id, 'restaurent_id' => $request->restaurent_id],
                //     ['free_delivery_amount' => $request->free_delivery_amount,'delivery_charge' => $request->delivery_charge]
                // );
                $areas = DeliveryCharges::where('id','!=',$id)->where('restaurant_id',$request->restaurant_id)->where('area_id',$request->area_id)->count();
                if($areas==0){
                    $charge->area_id = $request->area_id;
                    $charge->restaurant_id = $request->restaurant_id;
                    $charge->free_delivery_amount = $request->free_delivery_amount;
                    $charge->delivery_charge = $request->delivery_charge;
                    $result = $charge->save();
                    Flash::success(__('lang.updated_successfully', ['operator' => __('lang.delivery_charge')]));
                    return redirect(url('Delivery-Charges'));
                }else{
                    Flash::error('This restaurant have this place already,please try another !!');
                    return back();
                }
            
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
            return back();
        }
            
    }

    /**
     * Remove the specified RestaurantReview from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        // dd($id);
        $charge = DeliveryCharges::find($id);
        if($charge){
            $charge->delete();
        }
        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.delivery_charge')]));
        return redirect(url('Delivery-Charges'));
    }
}
