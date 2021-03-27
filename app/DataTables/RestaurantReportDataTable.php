<?php
/**
 * File name: RestaurantReportDataTable.php
 * Last modified: 2020.12.08 at 07:41:09
 * Author:  Diginest Solutions - https://diginestsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Restaurant;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Carbon;
class RestaurantReportDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('orders', function ($restaurant) {
                return $restaurant->total_orders;
            })
            ->editColumn('earnings', function ($restaurant) {
                return $restaurant->total_earning;
            })
            ->editColumn('date', function ($restaurant) {
                return getDateColumn($restaurant, 'created_at');
            })
            ->rawColumns($columns);

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Restaurant $model)
    {

        $start_date = $this->request()->get('start_date');
        $end_date = $this->request()->get('end_date');



        if (auth()->user()->hasRole('admin')) {

            $query = $model->newQuery()
                    ->join('earnings', "restaurant_id", "=", "restaurants.id")
                    ->select("restaurants.*","earnings.total_orders", "earnings.total_earning");
            if (!empty($start_date) && !empty($end_date)) {

                $start_date = Carbon::parse($start_date);
                $end_date = Carbon::parse($end_date);

                $query = $query->whereBetween('restaurants.created_at',[$start_date,$end_date]);
            }

            return $query;

        } else if (auth()->user()->hasRole('manager')){

            $query = $model->newQuery()
                ->join("user_restaurants", "restaurant_id", "=", "restaurants.id")
                ->join('earnings', "restaurant_id", "=", "restaurants.id")
                ->where('user_restaurants.user_id', auth()->id())
                ->groupBy("restaurants.id")
                ->select("restaurants.*","earnings.total_orders", "earnings.total_earning");
            
            if (!empty($start_date) && !empty($end_date)) {

                $start_date = Carbon::parse($start_date);
                $end_date = Carbon::parse($end_date);

                $query = $query->whereBetween('restaurants.created_at',[$start_date,$end_date]);
            }

            return $query;

        }else if(auth()->user()->hasRole('driver')){

            return $model->newQuery()
                ->join("driver_restaurants", "restaurant_id", "=", "restaurants.id")
                ->where('driver_restaurants.user_id', auth()->id())
                ->groupBy("restaurants.id")
                ->select("restaurants.*");

        } else if (auth()->user()->hasRole('client')) {

            return $model->newQuery()
                ->join("foods", "foods.restaurant_id", "=", "restaurants.id")
                ->join("food_orders", "foods.id", "=", "food_orders.food_id")
                ->join("orders", "orders.id", "=", "food_orders.order_id")
                ->where('orders.user_id', auth()->id())
                ->groupBy("restaurants.id")
                ->select("restaurants.*");

        } else {

            return $model->newQuery();

        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
                ]
            ));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = [
            
            [
                'data' => 'name',
                'name' => 'id',
                'title' => trans('lang.restaurant_name'),

            ],
            [
                'data' => 'orders',
                'title' => trans('lang.earning_total_orders'),

            ],
            [
                'data' => 'earnings',
                'title' => trans('lang.dashboard_total_earnings'),

            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.date'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Restaurant::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Restaurant::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.restaurant_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'restaurantsdatatable_' . time();
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }
}