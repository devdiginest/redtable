<?php

/**
 * File name: DriverReportDataTable.php
 * Last modified: 2020.12.08 at 11:35:00
 * Author:  Diginest Solutions - https://diginestsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Driver;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Carbon;

class DriverReportDataTable extends DataTable
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
            ->editColumn('user.name', function ($driver) {
                return getLinksColumnByRouteName([$driver->user], "users.edit", 'id', 'name');
            })
            ->editColumn('earning', function ($driver) {
                return getPriceColumn($driver, 'earning');
            })
            ->editColumn('available', function ($driver) {
                return getBooleanColumn($driver, 'available');
            })
            ->editColumn('date', function ($driver) {
                return getDateColumn($driver, 'created_at');
            })
            ->rawColumns($columns);

        return $dataTable;
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
                'data' => 'user.name',
                'title' => trans('lang.user_name'),

            ],
            [
                'data' => 'total_orders',
                'title' => trans('lang.driver_total_orders'),

            ],
            [
                'data' => 'earning',
                'title' => trans('lang.driver_earning'),

            ],
            [
                'data' => 'available',
                'title' => trans('lang.driver_available'),

            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.date'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Driver::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Driver::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.driver_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Driver $model)
    {

        $start_date = $this->request()->get('start_date');
        $end_date = $this->request()->get('end_date');

        if(auth()->user()->hasRole('admin')){
            $query = $model->newQuery()->with("user")->select('drivers.*');
            if (!empty($start_date) && !empty($end_date)) {

                $start_date = Carbon::parse($start_date);
                $end_date = Carbon::parse($end_date);
                $query = $query->whereBetween('drivers.created_at',[$start_date,$end_date]);
            }
            return $query;
        }else if (auth()->user()->hasRole('manager')){
            // restaurants of this user
            $restaurantsIds = array_column(auth()->user()->restaurants->toArray(), 'id');

            return $model->newQuery()->with("user")
                ->join('driver_restaurants','driver_restaurants.user_id','=','drivers.user_id')
                ->whereIn('driver_restaurants.restaurant_id',$restaurantsIds)
                ->distinct('driver_restaurants.user_id')
                ->select('drivers.*');
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
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'driversdatatable_' . time();
    }
}