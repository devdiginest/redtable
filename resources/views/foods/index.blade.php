@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.restaurant_menus')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.menu_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('foods.index') !!}">{{trans('lang.restaurant_menus')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.menu_table')}}</li>
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
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.menu_table')}}</a>
        </li>
        @can('foods.create')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('foods.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.add_dish')}}</a>
        </li>
        @endcan
        @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body">
      <form class="form-inline offset-3 position-absolute">
        <div class="form-row">
          <div class="form-group">
            <label for="rest_name" class="mx-2">{{trans('lang.restaurant_name_filter')}}</label>
            <select name="rest_name" id="rest_name" class="form-control mx-2 select-custom">
              <option value="">Choose</option>
              @foreach ($restaurant as $item)
              <option value="{{$item->id}}">{{$item->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-primary mx-2 btn-custom" id="generate">Generate</button>
            <button type="button" class="btn btn-danger mx-2 btn-custom" id="reset">Reset</button>
          </div>
        </div>
      </form>
      @include('foods.table')
      @push('scripts_lib')
      <script>
        const table = $("#dataTableBuilder");

        table.on('preXhr.dt', function(e,settings,data){
          data.rest_name = $("#rest_name").val();
        });

        $("#generate").on('click', function(){
            table.DataTable().ajax.reload();
            return false;
        });

        $("#reset").on('click', function(){
          
          table.on('preXhr.dt', function(e,settings,data){
            data.rest_name = '';
          });

          table.DataTable().ajax.reload();
            return false;
        });

      </script>
      @endpush
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection

