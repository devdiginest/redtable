<?php

namespace App\DataTables;

use App\Models\OrderStatus;
use App\Models\CustomField;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Order;
use Illuminate\Support\Carbon;

class TakeAwayReportDataTable extends DataTable
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
            
            ->editColumn('date',function($takeaway){
                return getDateColumn($takeaway,'created_at');
            })
            
            ->editColumn('orders.id', function ($takeaway) {
                $orderlist = Order::where('order_status_id', '=', $takeaway->order_status_id)->get();
                $orderCount = $orderlist->count();
                return $orderCount;
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(OrderStatus $model)
    {
        $start_date = $this->request()->get('start_date');
        $end_date = $this->request()->get('end_date');
        
        $query = $model->newQuery()
        ->join('orders', 'orders.order_status_id', '=', 'order_statuses.id' )
        ->selectRaw('order_statuses.id,orders.order_status_id,orders.created_at')->groupBy('order_statuses.id')
        ->where('orders.order_status_id', '=', 6);

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
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/'.app()->getLocale().'/datatable.json')
                        ),true)
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
                        'data' => 'orders.id',
                        'title' => trans('lang.driver_total_orders'),
                        'searchable'=>false,
                        
                    ],
                    [
                    'data' => 'created_at',
                    'title' => trans('lang.date'),
                    'searchable'=>false,
                    ]
            ];

        $hasCustomField = in_array(DeliveryAddress::class, setting('custom_field_models',[]));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', DeliveryAddress::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.delivery_address_' . $field->name),
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
        return 'takeawaydatatable_' . time();
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename().'.pdf');
    }
}