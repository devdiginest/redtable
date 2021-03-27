@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.report_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.report_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('restaurants.index') !!}">{{trans('lang.report_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.report_desc')}}</li>
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
    </div>
    <div class="card-body">
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{$restaurantsCount}}</h3>
            <p>{{$restaurantsCount > 1 ? trans('lang.branch_plural') : trans('lang.branch')}}</p>
          </div>
          <div class="icon">
              <i class="fa fa-cutlery"></i>
          </div>
          <a href="{{url('/reports/restaurants')}}" class="small-box-footer">{{trans('lang.full_report')}}
            <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{$driversCount}}</h3>
            <p>{{ $driversCount > 1 ? trans('lang.driver_plural') : trans('lang.driver') }}</p>
          </div>
          <div class="icon">
              <i class="fa fa-user"></i>
          </div>
          <a href="{{url('/reports/drivers')}}" class="small-box-footer">{{trans('lang.full_report')}}
            <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3 class="opacity">{{$driversCount}}</h3>
            <p>{{trans('lang.area')}}</p>
          </div>
          <div class="icon">
              <i class="fa fa-map-marker"></i>
          </div>
          <a href="{{url('/reports/area')}}" class="small-box-footer">{{trans('lang.full_report')}}
            <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{$driversCount}}</h3>
            <p>{{trans('lang.take_away')}}</p>
          </div>
          <div class="icon">
              <i class="fa fa-shopping-basket"></i>
          </div>
          <a href="{{url('/reports/takeaway')}}" class="small-box-footer">{{trans('lang.full_report')}}
            <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-light">
          <div class="inner">
            <h3>{{$driversCount}}</h3>
            <p>{{ $driversCount > 1 ? trans('lang.customer_plural') : trans('lang.customer')}}</p>
          </div>
          <div class="icon">
              <i class="fa fa-user"></i>
          </div>
          <a href="{{url('/reports/customers')}}" class="small-box-footer">{{trans('lang.full_report')}}
            <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>
@endsection

