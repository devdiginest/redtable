@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.report_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.report_driver_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{{url('/reports')}}">{{trans('lang.report_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.report_driver_desc')}}</li>
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
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.report_desc')}}</a>
        </li>
        @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body">
      <form class="offset-3">
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="start_date">Start Date</label>
              <input type="datetime-local" class="form-control" id="start_date">
            </div>
            <div class="form-group col-md-4">
              <label for="end_date">End Date</label>
              <input type="datetime-local" class="form-control" id="end_date">
            </div>
          </div>
          <div class="offset-3">
            <button type="button" class="btn btn-primary" id="generate">Generate</button>
            <button type="button" class="btn btn-danger" id="reset">Reset</button>
          </div>
        </form>
      @include('reports.rest_table')
      @push('scripts_lib')
      <script>
        const table = $("#dataTableBuilder");

        table.on('preXhr.dt', function(e,settings,data){
          data.start_date = $("#start_date").val();
          data.end_date = $("#end_date").val();
        });

        $("#generate").on('click', function(){
            table.DataTable().ajax.reload();
            return false;
        });

        $("#reset").on('click', function(){
          
          table.on('preXhr.dt', function(e,settings,data){
            data.start_date = '';
            data.end_date = '';
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