<?php

/**

 * File name: DeliveryChargeDataTable.php

 * Last modified: 2021.05.21 at 09:04:19

 * Author: Diginest Solutions - https://diginestsolutions.com

 * Copyright (c) 2021

 *

 */



namespace App\DataTables;




use App\Models\CustomField;

use App\Models\DeliveryCharges;


use Barryvdh\DomPDF\Facade as PDF;

use Yajra\DataTables\EloquentDataTable;

use Yajra\DataTables\Services\DataTable;



/**

 * Class DeliveryChargeDataTable

 * @package App\DataTables

 */

class DeliveryChargeDataTable extends DataTable

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

            ->editColumn('area_id', function ($DeliveryCharge) {
                // dd($DeliveryCharge->area->name);
                return $DeliveryCharge->area->name;

            })
            ->editColumn('restaurant_id', function ($DeliveryCharge) {

                return $DeliveryCharge->restaurant->name;

            })
            // ->editColumn('Free Delivery Amount', function ($DeliveryCharge) {

            //     return getArrayColumn($DeliveryCharge->free_delivery_amount,'Free Delivery Amount');

            // })
            // ->editColumn('Delivery Charge', function ($DeliveryCharge) {

            //     return getArrayColumn($DeliveryCharge->delivery_charge,'Delivery Charge');

            // })
            ->addColumn('action', 'deliverycharge.datatables_actions')

            ->rawColumns(array_merge($columns, ['action']));


        return $dataTable;

    }



    /**

     * Get query source of dataTable.

     *

     * @param \App\Models\DeliveryCharge $model

     * @return \Illuminate\Database\Eloquent\Builder

     * @throws \Prettus\Repository\Exceptions\RepositoryException

     */

    
    public function query(DeliveryCharges $model)
    {
        return $model->newQuery()->with('area','restaurant');
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

     * Get columns.

     *

     * @return array

     */

    protected function getColumns()

    {

        $columns = [

            [

                'data' => 'restaurant_id',

                'title' => trans('lang.restaurant'),



            ],
            [

                'data' => 'area_id',

                'title' => trans('lang.areaname'),



            ],

            [

                'data' => 'free_delivery_amount',

                'title' => trans('lang.free_delivery_amount'),



            ],

            [

                'data' => 'delivery_charge',

                'title' => trans('lang.delivery_charge'),



            ]

        ];



        $hasCustomField = in_array(DeliveryCharge::class, setting('custom_field_models', []));

        if ($hasCustomField) {

            $customFieldsCollection = CustomField::where('custom_field_model', DeliveryCharge::class)->where('in_table', '=', true)->get();

            foreach ($customFieldsCollection as $key => $field) {

                array_splice($columns, $field->order - 1, 0, [[

                    'data' => 'custom_fields.' . $field->name . '.view',

                    'title' => trans('lang.deliverycharge_plural_' . $field->name),

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

        return 'delivery_charge_datatable_' . time();

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