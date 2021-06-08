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
        <h1 class="m-0 text-dark">{{trans('lang.stock_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.stock_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{{url('/stocks')}}">{{trans('lang.stock_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.stock_create')}}</li>
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
        <li class="nav-item">
          <a class="nav-link" href="{{url('/stocks')}}"><i class="fa fa-list mr-2"></i>{{trans('lang.stock_table')}}</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.stock_create')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <div class="row">
        <form method="post" action="{{url('/stocks')}}">
          @csrf
          <div class="form-group">
            <label for="restname"> Restaurant Name </label>
            {!! Form::select('restaurants', $restaurant, null, ['class' => 'form-control', 'id' => 'restname', 'placeholder'=>  trans("lang.stock_name_placeholder")]) !!}
          </div>
          <div class="form-group">
            <label for="foodname"> Food Name </label>
            {!! Form::select('foods', $food, null, ['class' => 'form-control', 'id' => 'foods', 'placeholder'=>  trans("lang.stock_name_placeholder")]) !!}
          </div>
          <div class="form-group">
            <label for="quantity"> Quantity </label>
            <input type="text" class="form-control" name="quantity" />
          </div>
          <div class="form-group">
            <label for="status"> Status </label>
            <select name="status" class="form-control">
              <option value="0">Yes</option>
              <option value="1">No</option>
            </select>
          </div>
          <!-- Submit Field -->
          <div class="form-group col-12 text-right">
              <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.stock')}}</button>
              <a href="{{url('/stocks')}}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
          </div>
        </form>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection
@push('scripts_lib')
<script type="text/javascript">

              $('#restname').on('change', function() {
                var url = "{{url('/stocks')}}";
                $('#foods').empty();
                var id = $('#restname').val();
                // $('#foods').html('<option selected="selected" value="">Loading...</option>');
                var url = url + '/getfood/'+id;
                //console.log(url);
                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    success:function (response) {
                    var len = 0;
                    if (response.data != null) {
                        len = response.data.length;
                    }

                    if (len>0) {
                        for (var i = 0; i<len; i++) {
                             var id = response.data[i].food_id;
                             var name = response.data[i].name;

                             var option = "<option value='"+id+"'>"+name+"</option>"; 

                             $("#foods").append(option);
                        }
                    }
                }
                });
              });
            </script>
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
</script>
@endpush