@extends('layouts.app')
@push('css_lib')
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<!-- select2 -->
<link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
<!-- bootstrap wysihtml5 - text editor -->
<link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
{{--dropzone--}}
<link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.deliverycharge_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.deliverycharge_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('restaurants.index') !!}">{{trans('lang.deliverycharge_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.deliverycharge_create')}}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <!-- @can('restaurants.index') -->
        <li class="nav-item">
          <a class="nav-link" href="{!! url('Delivery-Charges') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.deliverycharge_table')}}</a>
        </li>
        <!-- @endcan -->
        <!-- @can('restaurants.create') -->
        <li class="nav-item">
          <a class="nav-link active" href="{!! url('Delivery_Charge/Create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.deliverycharge_create')}}</a>
        </li>
        <!-- @endcan -->
      </ul>
    </div>
    <div class="card-body">
      {!! Form::model($charge, ['url' => ['Delivery_Charge/save'], 'method' => 'post']) !!}
      <div class="row">
        @include('deliverycharge.fields')
      </div>
      {!! Form::close() !!}
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection
@push('scripts_lib')
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<!-- select2 -->
<script src="{{asset('plugins/select2/select2.min.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
{{--dropzone--}}
<script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
    $('#RestID').change(function() {
        var id = $(this).val();
        var origin   = window.location.origin;
        // AJAX request 
        $.ajax({
           url: origin+'/GetArea/'+id,
           type: 'get',
           dataType: 'json',
           success: function(response){
            $("#AreaID").empty();
            console.log(response['data']);
             var len = 0;
             if(response['data'] != null){
               len = response['data'].length;
             }

             if(len > 0){
               // Read data and create <option >
               for(var i=0; i<len; i++){

                 var id = response['data'][i].id;
                 var name = response['data'][i].name;

                 var option = "<option value='"+id+"'>"+name+"</option>"; 

                 $("#AreaID").append(option); 
               }
             }

           }
        });
    });
</script>
@endpush