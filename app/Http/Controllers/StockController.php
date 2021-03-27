<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Stock;

use App\Repositories\CategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\FoodRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       //$stocks = Stock::all(); 
       $stocks = DB::table('stocks')
                ->select('stocks.id','stocks.food_id','stocks.restaurant_id','foods.name as food_name','restaurants.name as rest_name','stocks.quantity','stocks.updated_at')
                ->join('foods','foods.id','=','stocks.food_id')
                ->join('restaurants','restaurants.id','=','stocks.restaurant_id')
                ->get();
       return view('stocks.index', ["stocks"=>$stocks]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $food = $this->foodRepository->pluck('name', 'id');
        if (auth()->user()->hasRole('admin')) {
            $restaurant = $this->restaurantRepository->pluck('name', 'id');
        } else {
            $restaurant = $this->restaurantRepository->myActiveRestaurants()->pluck('name', 'id');
        }
        return view('stocks.create')->with("food", $food)->with("restaurant", $restaurant);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $stock = new Stock();
        $stock->food_id = $request->foods;
        $stock->restaurant_id = $request->restaurants;
        $stock->quantity = $request->quantity;
        $stock->save();

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.stock')]));

        return redirect('/stocks');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $stock = Stock::find($id);
        $food = $this->foodRepository->where('id',$stock->food_id)->first('name');
        $restaurant = $this->restaurantRepository->where('id',$stock->restaurant_id)->first('name');
        return view("stocks.edit")->with('stock', $stock)->with('food', $food)->with('restaurant', $restaurant);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $stock = Stock::find($id);
        $stock->quantity = $request->quantity;
        $stock->save();

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.stock')]));

        return redirect('/stocks');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $stock = Stock::find($id);
        $stock->delete();
        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.stock')]));

        return redirect('/stocks');
    }

    public function getFood($id)
    {
       $stocks = DB::table('restaurant_foods')
                ->select('restaurant_foods.food_id','foods.name')
                ->join('foods','foods.id','=','restaurant_foods.food_id')
                ->where('restaurant_foods.restaurant_id',$id)
                ->get();
       
       return response()->json(['data' => $stocks]);
    }
}
