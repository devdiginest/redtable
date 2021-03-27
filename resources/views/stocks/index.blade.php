@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.stock_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.stock_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{{url('/stocks')}}">{{trans('lang.stock_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.stock_table')}}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.stock_table')}}</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{url('/stocks/create')}}"><i class="fa fa-plus mr-2"></i>{{trans('lang.stock_create')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
    	<table class="table dataTable no-footer dtr-inline">
    		<thead>
    			<tr role="row">
    				<th>Food Name</th>
		    		<th>Restaurant Name</th>
		    		<th>Quantity</th>
		    		<th>Updated At</th>
		    		<th>Actions</th>
    			</tr>
    		</thead>
    		<tbody>
    			@foreach($stocks as $stock)
	    			<tr>
	    				<td>{{ $stock->food_name }}</td>
	    				<td>{{ $stock->rest_name }}</td>
	    				<td>{{ $stock->quantity }}</td>
	    				<td>{{ \Carbon\Carbon::parse($stock->updated_at)->diffForHumans() }}</td>
	    				<td>
	    					<div class='btn-group btn-group-sm'>
							  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.stock_edit')}}" href="{{ url('/stocks/'.$stock->id.'/edit') }}" class='btn btn-link'>
							    <i class="fa fa-edit"></i>
							  </a>
							  <form method="post" action="{{url('/stocks/'.$stock->id)}}">
							  	@csrf
							  	@method("DELETE")
							  	
							  	{!! Form::button('<i class="fa fa-trash"></i>', [
									  'type' => 'submit',
									  'class' => 'btn btn-link text-danger',
									  'onclick' => "return confirm('Are you sure?')"
									  ]) !!}
							  </form>
							  
							</div>
	    				</td>
	    			</tr>
    			@endforeach
    		</tbody>
    	</table>

      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection

