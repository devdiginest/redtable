<?php
/**
 * File name: FoodController.php
 * Last modified: 2021.03.26 at 08:21:08
 * Author: Diginest solutions - https://diginestsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

use App\Criteria\Foods\FoodsOfUserCriteria;
use App\DataTables\FoodDataTable;
use App\Http\Requests\CreateFoodRequest;
use App\Http\Requests\UpdateFoodRequest;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\FoodRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Prettus\Validator\Exceptions\ValidatorException;

use App\Models\Meal;
use App\Models\RestaurantCategory;
use App\Stock;

class FoodController extends Controller
{
    /** @var  FoodRepository */
    private $foodRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var RestaurantRepository
     */
    private $restaurantRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(FoodRepository $foodRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , RestaurantRepository $restaurantRepo
        , CategoryRepository $categoryRepo)
    {
        parent::__construct();
        $this->foodRepository = $foodRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->restaurantRepository = $restaurantRepo;
        $this->categoryRepository = $categoryRepo;
    }

    /**
     * Display a listing of the Food.
     *
     * @param FoodDataTable $foodDataTable
     * @return Response
     */
    public function index(FoodDataTable $foodDataTable)
    {
        $restaurant = $this->restaurantRepository->get();
        return $foodDataTable->render('foods.index',['restaurant'=>$restaurant]);
    }

    /**
     * Show the form for creating a new Food.
     *
     * @return Response
     */
    public function create()
    {

        $category = $this->categoryRepository->pluck('name', 'id');
        if (auth()->user()->hasRole('admin')) {
            $restaurant = $this->restaurantRepository->pluck('name', 'id');
        } else {
            $restaurant = $this->restaurantRepository->myActiveRestaurants()->pluck('name', 'id');
        }

        $meal = Meal::pluck('name', 'id');

        $restaurantsSelected = [];
        $mealsSelected = [];
        $hasCustomField = in_array($this->foodRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->foodRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('foods.create')->with("customFields", isset($html) ? $html : false)
        ->with("restaurant", $restaurant)
        ->with('restaurantsSelected',$restaurantsSelected)
        ->with("meal", $meal)
        ->with('mealsSelected',$mealsSelected)
        ->with("category", $category);
    }

    /**
     * Store a newly created Food in storage.
     *
     * @param CreateFoodRequest $request
     *
     * @return Response
     */
    public function store(CreateFoodRequest $request)
    {
        $input = $request->all();
        //return($input);
        $restArray = $input['restaurants'];
        
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->foodRepository->model());
        try {
            


            $food = $this->foodRepository->create($input);

            foreach ($restArray as $restaurants) {

                $resCats = RestaurantCategory::where('restaurant_id',$restaurants)->where('category_id',$input['category_id'])->first();

                if($resCats == null){
                    $resCategory = new RestaurantCategory;
                    $resCategory->category_id = $input['category_id'];
                    $resCategory->restaurant_id = $restaurants;
                    $resCategory->save();
                }

                $stock = new Stock;
                $stock->food_id = $food->id;
                $stock->restaurant_id = $restaurants;
                $stock->quantity = 0;
                $stock->save();
            }


            $food->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($food, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.food')]));

        return redirect(route('foods.index'));
    }

    /**
     * Display the specified Food.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function show($id)
    {
        $this->foodRepository->pushCriteria(new FoodsOfUserCriteria(auth()->id()));
        $food = $this->foodRepository->findWithoutFail($id);

        if (empty($food)) {
            Flash::error('Food not found');

            return redirect(route('foods.index'));
        }
        dd($food);
        return view('foods.show')->with('food', $food);
    }

    /**
     * Show the form for editing the specified Food.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function edit($id)
    {
        $this->foodRepository->pushCriteria(new FoodsOfUserCriteria(auth()->id()));
        $food = $this->foodRepository->findWithoutFail($id);
        if (empty($food)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.food')]));
            return redirect(route('foods.index'));
        }
        $category = $this->categoryRepository->pluck('name', 'id');

        $meal = Meal::pluck('name', 'id');

        if (auth()->user()->hasRole('admin')) {
            $restaurant = $this->restaurantRepository->pluck('name', 'id');
        } else {
            $restaurant = $this->restaurantRepository->myRestaurants()->pluck('name', 'id');
        }
        $restaurantsSelected = $food->restaurants()->pluck('restaurants.id')->toArray();
        $mealsSelected = $food->meals()->pluck('meals.id')->toArray();
        $customFieldsValues = $food->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->foodRepository->model());
        $hasCustomField = in_array($this->foodRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('foods.edit')->with('food', $food)->with("customFields", isset($html) ? $html : false)
        ->with("restaurant", $restaurant)
        ->with('restaurantsSelected', $restaurantsSelected)
        ->with("meal", $meal)
        ->with('mealsSelected',$mealsSelected)
        ->with("category", $category);
    }

    /**
     * Update the specified Food in storage.
     *
     * @param int $id
     * @param UpdateFoodRequest $request
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function update($id, UpdateFoodRequest $request)
    {
        $this->foodRepository->pushCriteria(new FoodsOfUserCriteria(auth()->id()));
        $food = $this->foodRepository->findWithoutFail($id);

        if (empty($food)) {
            Flash::error('Food not found');
            return redirect(route('foods.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->foodRepository->model());
        try {
            $food = $this->foodRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($food, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $food->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.food')]));

        return redirect(route('foods.index'));
    }

    /**
     * Remove the specified Food from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $this->foodRepository->pushCriteria(new FoodsOfUserCriteria(auth()->id()));
            $food = $this->foodRepository->findWithoutFail($id);

            if (empty($food)) {
                Flash::error('Food not found');

                return redirect(route('foods.index'));
            }

            DB::delete('delete from restaurant_foods where restaurant_id  = ?',[$id]);

            $this->foodRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.food')]));

        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('foods.index'));
    }

    /**
     * Remove Media of Food
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $food = $this->foodRepository->findWithoutFail($input['id']);
        try {
            if ($food->hasMedia($input['collection'])) {
                $food->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
