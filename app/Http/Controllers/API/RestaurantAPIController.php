<?php
/**
 * File name: RestaurantAPIController.php
 * Last modified: 2020.05.04 at 09:04:19
 * Author: Diginest - http://diginestsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\Response;


use App\Criteria\Restaurants\ActiveCriteria;
use App\Criteria\Restaurants\RestaurantsOfCuisinesCriteria;
use App\Criteria\Restaurants\NearCriteria;
use App\Criteria\Restaurants\PopularCriteria;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Food;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class RestaurantController
 * @package App\Http\Controllers\API
 */
class RestaurantAPIController extends Controller
{
    /** @var  RestaurantRepository */
    private $restaurantRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;


    public function __construct(RestaurantRepository $restaurantRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->restaurantRepository = $restaurantRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;

    }

    /**
     * Display a listing of the Restaurant.
     * GET|HEAD /restaurants
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->restaurantRepository->pushCriteria(new RequestCriteria($request));
            $this->restaurantRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->restaurantRepository->pushCriteria(new RestaurantsOfCuisinesCriteria($request));
            if ($request->has('popular')) {
                $this->restaurantRepository->pushCriteria(new PopularCriteria($request));
            } else {
                $this->restaurantRepository->pushCriteria(new NearCriteria($request));
            }
            $this->restaurantRepository->pushCriteria(new ActiveCriteria());
            $restaurants = $this->restaurantRepository->all();

            foreach ($restaurants as $restaurant) {

                $restaurant->description = strip_tags($restaurant->description);
                $restaurant->working_hours = strip_tags($restaurant->working_hours);
            }

        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($restaurants->toArray(), 'Restaurants retrieved successfully');
    }

    /**
     * Display Restaurant Categories.
     * GET|HEAD /restaurant_categories/{rid}
     *
     * @param int $rid
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getcategories($rid){
        // $categories = Restaurant::where('id',$rid)->with('categories')->get();
        $categories = Category::join('restaurant_categories','categories.id','=','restaurant_categories.category_id')
                ->where('restaurant_categories.restaurant_id','=',$rid)->get();
        foreach ($categories as $category) {

                $category->description = strip_tags($category->description);
            }
        return $this->sendResponse($categories->toArray(),'Restaurant categories retrieved successfully');
    }

    /**
     * Display Restaurant Foods.
     * GET|HEAD /restaurant_foods/{rid}
     *
     * @param int $rid
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getfoods($rid){
        //  $foods = Restaurant::where('id',$rid)->with('foods')->get();
        // $foods = Category::join('restaurant_categories','categories.id','=','restaurant_categories.category_id')
        //         ->where('restaurant_categories.restaurant_id','=',$rid)
        //         ->with('foods')->get();
        $foods = Food::join('restaurant_foods','foods.id','=','restaurant_foods.food_id')
        ->where('restaurant_foods.restaurant_id','=',$rid)->get();
        foreach ($foods as $food ){
            // code...
            $food->food_id = $food->id;
            $food->description = strip_tags($food->description);
            $food->ingredients = strip_tags($food->ingredients);
        }
        return $this->sendResponse($foods->toArray(),'Restaurant foods retrieved successfully');
    }

    /**
     * Display Restaurants Featured Foods.
     * GET|HEAD /restaurant_featured/{rid}
     *
     * @param int $rid
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getfeatured($rid){
        //  $foods = Restaurant::where('id',$rid)->with('foods')->get();
        // $foods = Category::join('restaurant_categories','categories.id','=','restaurant_categories.category_id')
        //         ->where('restaurant_categories.restaurant_id','=',$rid)
        //         ->with('foods')->get();
        $foods = Food::join('restaurant_foods','foods.id','=','restaurant_foods.food_id')
        ->where('restaurant_foods.restaurant_id','=',$rid)
        ->where('foods.featured','=','1')->get();
        foreach ($foods as $food) {
            $food->description = strip_tags($food->description);
            $food->ingredients = strip_tags($food->ingredients);
        }
        return $this->sendResponse($foods->toArray(),'Restaurant featured foods retrieved successfully');
    }

    /**
     * Display Restaurants Todays Special Foods.
     * GET|HEAD /restaurant_specialfoods/{rid}
     *
     * @param int $rid
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function get_todays_special($rid){
        
        $foods = Food::join('restaurant_foods','foods.id','=','restaurant_foods.food_id')
        ->where('restaurant_foods.restaurant_id','=',$rid)
        ->where('foods.todays_special','=','1')->get();

        foreach ($foods as $food) {

            $food->description = strip_tags($food->description);
            $food->ingredients = strip_tags($food->ingredients);
        }
        return $this->sendResponse($foods->toArray(),'Restaurant todays special foods retrieved successfully');
    }

    /**
     * Display Restaurant Details.
     * GET|HEAD /restaurantdetails/{rid}
     *
     * @param int $rid
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getdetails($rid){
        
        $restaurants = Restaurant::where('id',$rid)
                    ->with('categories')
                    ->with('restaurantReviews')
                    ->with('foods')
                    ->get();
        foreach ($restaurants[0]->foods as $rest) {
            $rest->food_id = $rest->id;
            $rest->description = strip_tags($rest->description);
            $rest->ingredients = strip_tags($rest->ingredients);
        }
        foreach ($restaurants as $restaurant) {
            $restaurant->description = strip_tags($restaurant->description);
            $restaurant->working_hours = strip_tags($restaurant->working_hours);
        }
        foreach ($restaurants[0]->categories as $category) {
            $category->description = strip_tags($category->description);
        }
                    
        return $this->sendResponse($restaurants[0],'Restaurant retrieved successfully');
    }

    /**
     * Display Restaurant Best Offers in Food.
     * GET|HEAD /restaurant_bestoffers/{rid}
     *
     * @param int $rid
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function get_offers($rid){
        
       $foods = Food::join('restaurant_foods','foods.id','=','restaurant_foods.food_id')
        ->where('restaurant_foods.restaurant_id','=',$rid)->get();

        foreach($foods as $food){
            if($food->discount_price != NULL){
                $diffAmount = $food->price - $food->discount_price;
                if($diffAmount > 0) {
                    $division = $diffAmount/$food->discount_price;
                    $percentage = $division * 100;
                    $food->discount_percentage = round($percentage);
                }
            }

            $food->description = strip_tags($food->description);
            $food->ingredients = strip_tags($food->ingredients);
            
            
        }
                    
        return $this->sendResponse($foods->toArray(),'Restaurant Foods retrieved successfully');
    }


    /**
     * Display the specified Restaurant.
     * GET|HEAD /restaurants/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        /** @var Restaurant $restaurant */
        if (!empty($this->restaurantRepository)) {
            try {
                $this->restaurantRepository->pushCriteria(new RequestCriteria($request));
                $this->restaurantRepository->pushCriteria(new LimitOffsetCriteria($request));
                if ($request->has(['myLon', 'myLat', 'areaLon', 'areaLat'])) {
                    $this->restaurantRepository->pushCriteria(new NearCriteria($request));
                }
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }
            $restaurant = $this->restaurantRepository->findWithoutFail($id);
        }

        if (empty($restaurant)) {
            return $this->sendError('Restaurant not found');
        }

        foreach ($restaurant as $rest) {
            // code...
            $rest->description = strip_tags($rest->description);
            $rest->working_hours = strip_tags($rest->working_hours);
        }

        return $this->sendResponse($restaurant->toArray(), 'Restaurant retrieved successfully');
    }

    /**
     * Store a newly created Restaurant in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();
        if (auth()->user()->hasRole('manager')) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->restaurantRepository->model());
        try {
            $restaurant = $this->restaurantRepository->create($input);
            $restaurant->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($restaurant, 'image');
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($restaurant->toArray(), __('lang.saved_successfully', ['operator' => __('lang.restaurant')]));
    }

    /**
     * Update the specified Restaurant in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $restaurant = $this->restaurantRepository->findWithoutFail($id);

        if (empty($restaurant)) {
            return $this->sendError('Restaurant not found');
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->restaurantRepository->model());
        try {
            $restaurant = $this->restaurantRepository->update($input, $id);
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['drivers'] = isset($input['drivers']) ? $input['drivers'] : [];
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($restaurant, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $restaurant->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($restaurant->toArray(), __('lang.updated_successfully', ['operator' => __('lang.restaurant')]));
    }

    /**
     * Remove the specified Restaurant from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $restaurant = $this->restaurantRepository->findWithoutFail($id);

        if (empty($restaurant)) {
            return $this->sendError('Restaurant not found');
        }

        $restaurant = $this->restaurantRepository->delete($id);

        return $this->sendResponse($restaurant, __('lang.deleted_successfully', ['operator' => __('lang.restaurant')]));
    }
}
