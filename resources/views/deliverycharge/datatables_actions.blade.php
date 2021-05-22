<div class='btn-group btn-group-sm'>
        <!-- @can('restaurantReviews.edit') -->
            <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.edit_delivery_charge')}}" href="{{ url('Delivery_Charge') }}/{{$id}}/{{('edit')}}" class='btn btn-link'>
                <i class="fa fa-edit"></i> </a>
        <!-- @endcan -->

        <!-- @can('restaurantReviews.destroy') -->
            {!! Form::open(['url' => ['Delivery_Charge/destroy', $id], 'method' => 'get']) !!}
            {!! Form::button('<i class="fa fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Are you sure?')"
            ]) !!}
            {!! Form::close() !!}
        <!-- @endcan -->
</div>
