<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Repositories\RestaurantRepository;
use App\Repositories\DriverRepository;
use App\DataTables\RestaurantReportDataTable;
use App\DataTables\DriverReportDataTable;
use App\DataTables\CustomerReportDataTable;
use App\DataTables\AreaReportDataTable;
use App\DataTables\TakeAwayReportDataTable;

class ReportController extends Controller
{
    /** @var  RestaurantRepository */
    private $restaurantRepository;

    /**
     * @var DriverRepository
     */
    private $driverRepository;

    public function __construct(RestaurantRepository $restaurantRepo, DriverRepository $driverRepo)
    {
        parent::__construct();
        $this->restaurantRepository = $restaurantRepo;
        $this->driverRepository = $driverRepo;
    }
    
    //
    public function index(){
        $restaurantsCount = $this->restaurantRepository->count();
        $driversCount = $this->driverRepository->count();
        return view('reports.index')
                ->with('restaurantsCount', $restaurantsCount)
                ->with('driversCount', $driversCount);
    }

    public function restaurants(RestaurantReportDataTable $restaurantreportDataTable)
    {
        return $restaurantreportDataTable->render('reports.restaurant_report');
    }

    public function drivers(DriverReportDataTable $driverreportDataTable){
        return $driverreportDataTable->render('reports.driver_report');
    }

    public function areaWise(AreaReportDataTable $areareportDataTable)
    {
        return $areareportDataTable->render('reports.area_report');
    }
    
    public function takeAway(TakeAwayReportDataTable $takeawayreportDataTable)
    {
        return $takeawayreportDataTable->render('reports.takeaway_report');
    }

    public function customers(CustomerReportDataTable $customerreportDataTable)
    {
        return $customerreportDataTable->render('reports.customer_report');
    }
}
