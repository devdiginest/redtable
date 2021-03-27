<?php

/**
 * File name: CustomerReportDataTable.php
 * Last modified: 2020.12.08 at 12:39:00
 * Author:  Diginest Solutions - https://diginestsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use App\Models\Order;
use Illuminate\Support\Carbon;

class CustomerReportDataTable extends DataTable
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
        
        return $dataTable
            ->editColumn('email', function ($user) {
                //dd($user);
                return getEmailColumn($user, 'email');
            })
            ->editColumn('orders', function ($user) {
                $orderlist = Order::where('user_id', '=', $user->user_id)->get();
                $orderCount = $orderlist->count();
                return $orderCount;
            })
            ->editColumn('date', function ($driver) {
                return getDateColumn($driver, 'created_at');
            })
            ->rawColumns($columns);
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
                'title' => trans('lang.user_name'),

            ],
            [
                'data' => 'email',
                'title' => trans('lang.user_email'),

            ],
            [
                'data' => 'orders',
                'title' => trans('lang.driver_total_orders'),
            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.date'),
                'searchable' => false,
            ]
        ];

        // TODO custom element generator
        $hasCustomField = in_array(User::class, setting('custom_field_models',[]));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', User::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.user_' . $field->name),
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
     * @param \App\Models\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $start_date = $this->request()->get('start_date');
        $end_date = $this->request()->get('end_date');

       $query = $model->newQuery()
                ->with('roles')
                ->join('orders','orders.user_id', '=', 'users.id')
                ->whereHas(
                    'roles', function($q){
                        $q->where('name', 'client');
                    }
                );
        if (!empty($start_date) && !empty($end_date)) {

            $start_date = Carbon::parse($start_date);
            $end_date = Carbon::parse($end_date);
            $query = $query->whereBetween('orders.created_at',[$start_date,$end_date]);
        }
        return $query;
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
            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
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
        return 'customersdatatable_' . time();
    }
}