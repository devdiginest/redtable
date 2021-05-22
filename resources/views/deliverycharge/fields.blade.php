@if($customFields)
<h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

<!-- Restaurant Id Field -->
<div class="form-group row ">
  {!! Form::label('restaurant_id', trans("lang.restaurant"),['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <?php 
        asort($restaurants); 
        if($charge!=null){
            $restaurant =  $charge->restaurant_id;
        }else{
            $restaurant = null;
        }
    ?>
    {!! Form::select('restaurant_id', $restaurants, $restaurant, ['placeholder' => 'Select Restaurant','class' => 'select2 form-control','id' => 'RestID']) !!}
    
  </div>
</div>

<!-- User Id Field -->
<div class="form-group row ">
  {!! Form::label('area_id', trans("lang.areaname"),['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <?php 
            asort($areas); 
        if($charge!=null){
            $area =  $charge->area_id;
        }else{
            $area = null;
        }
    ?>
    {!! Form::select('area_id', $areas, $area, ['placeholder' => 'Select Area','class' => 'select2 form-control','id' => 'AreaID']) !!}
  </div>
</div>




<!-- free_delivery_amount Field -->
<div class="form-group row ">
  {!! Form::label('Free Delivery Amount', trans("lang.free_delivery_amount"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::number('free_delivery_amount', null,  ['class' => 'form-control','placeholder'=>  trans("lang.free_delivery_amount")]) !!}
    
  </div>
</div>

    <!-- delivery_charge -->
<div class="form-group row ">
  {!! Form::label('Delivery Charge', trans("lang.delivery_charge"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::number('delivery_charge', null,  ['class' => 'form-control','placeholder'=>  trans("lang.delivery_charge")]) !!}
    
  </div>
</div>
</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">


</div>
@if($customFields)
<div class="clearfix"></div>
<div class="col-12 custom-field-container">
  <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
  {!! $customFields !!}
</div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 text-right">
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.delivery_charge')}}</button>
  <a href="{!! url('Delivery-Charges') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>