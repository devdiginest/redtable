@if($customFields)
<h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
<!-- Code Field -->
<div class="form-group row ">
  {!! Form::label('code', trans("lang.coupon_code"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    @if(isset($coupon['code']))
      <p>{!! $coupon->code !!}</p>
    @else
      {!! Form::text('code', null,  ['class' => 'form-control','placeholder'=>  trans("lang.coupon_code_placeholder")]) !!}
      <div class="form-text text-muted">
        {{ trans("lang.coupon_code_help") }}
      </div>
      @endif
  </div>
</div>

<!-- Discount Type Field -->
<div class="form-group row ">
  {!! Form::label('discount_type', trans("lang.coupon_discount_type"),['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('discount_type', ['percent' => trans('lang.coupon_percent'),'fixed' => trans('lang.coupon_fixed')], null, ['class' => 'select2 form-control']) !!}
    <div class="form-text text-muted">{{ trans("lang.coupon_discount_type_help") }}</div>
  </div>
</div>

<!-- Discount Field -->
<div class="form-group row ">
  {!! Form::label('discount', trans("lang.coupon_discount"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::number('discount', null,  ['class' => 'form-control','placeholder'=>  trans("lang.coupon_discount_placeholder"),'step'=>"any", 'min'=>"0"]) !!}
    <div class="form-text text-muted">
      {!! trans("lang.coupon_discount_help")   !!}
    </div>
  </div>
</div>

<!-- Image Field -->
<div class="form-group row">
  {!! Form::label('image', trans("lang.category_image"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <div style="width: 100%" class="dropzone image" id="image" data-field="image">
      <input type="hidden" name="image">
    </div>
    <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
    <div class="form-text text-muted w-50">
      {{ trans("lang.category_image_help") }}
    </div>
  </div>
</div>
@prepend('scripts')
<script type="text/javascript">
    var var15866134771240834480ble = '';
    @if(isset($coupon) && $coupon->hasMedia('image'))
    var15866134771240834480ble = {
        name: "{!! $coupon->getFirstMedia('image')->name !!}",
        size: "{!! $coupon->getFirstMedia('image')->size !!}",
        type: "{!! $coupon->getFirstMedia('image')->mime_type !!}",
        collection_name: "{!! $coupon->getFirstMedia('image')->collection_name !!}"};
    @endif
    var dz_var15866134771240834480ble = $(".dropzone.image").dropzone({
        url: "{!!url('uploads/store')!!}",
        addRemoveLinks: true,
        maxFiles: 1,
        init: function () {
        @if(isset($coupon) && $coupon->hasMedia('image'))
            dzInit(this,var15866134771240834480ble,'{!! url($coupon->getFirstMediaUrl('image','thumb')) !!}')
        @endif
        },
        accept: function(file, done) {
            dzAccept(file,done,this.element,"{!!config('medialibrary.icons_folder')!!}");
        },
        sending: function (file, xhr, formData) {
            dzSending(this,file,formData,'{!! csrf_token() !!}');
        },
        maxfilesexceeded: function (file) {
            dz_var15866134771240834480ble[0].mockFile = '';
            dzMaxfile(this,file);
        },
        complete: function (file) {
            dzComplete(this, file, var15866134771240834480ble, dz_var15866134771240834480ble[0].mockFile);
            dz_var15866134771240834480ble[0].mockFile = file;
        },
        removedfile: function (file) {
            dzRemoveFile(
                file, var15866134771240834480ble, '{!! url("coupon/remove-media") !!}',
                'image', '{!! isset($coupon) ? $coupon->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
            );
        }
    });
    dz_var15866134771240834480ble[0].mockFile = var15866134771240834480ble;
    dropzoneFields['image'] = dz_var15866134771240834480ble;
</script>
@endprepend



<!-- Description Field -->
<div class="form-group row ">
  {!! Form::label('description', trans("lang.coupon_description"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
     trans("lang.coupon_description_placeholder")  ]) !!}
    <div class="form-text text-muted">{{ trans("lang.coupon_description_help") }}</div>
  </div>
</div>

</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

  <!-- Food Id Field -->
  <div class="form-group row ">
    {!! Form::label('foods[]', trans("lang.coupon_food_id"),['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::select('foods[]', $food, $foodsSelected, ['class' => 'select2 form-control', 'multiple'=>'multiple']) !!}
      <div class="form-text text-muted">{{ trans("lang.coupon_food_id_help") }}</div>
    </div>
  </div>

<!-- Restaurant Id Field -->
<div class="form-group row ">
  {!! Form::label('restaurants', trans("lang.coupon_restaurant_id"),['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('restaurants[]', $restaurant, $restaurantsSelected, ['class' => 'select2 form-control', 'multiple'=>'multiple']) !!}
    <div class="form-text text-muted">{{ trans("lang.coupon_restaurant_id_help") }}</div>
  </div>
</div>


<!-- Category Id Field -->
<div class="form-group row ">
  {!! Form::label('categories[]', trans("lang.coupon_category_id"),['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('categories[]', $category, $categoriesSelected, ['class' => 'select2 form-control', 'multiple'=>'multiple']) !!}
    <div class="form-text text-muted">{{ trans("lang.coupon_category_id_help") }}</div>
  </div>
</div>


<!-- Expires At Field -->
<div class="form-group row ">
  {!! Form::label('expires_at', trans("lang.coupon_expires_at"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
      {!! Form::text('expires_at', null,  ['class' => 'form-control datepicker','autocomplete'=>'off','placeholder'=>  trans("lang.coupon_expires_at_placeholder")  ]) !!}
    <div class="form-text text-muted">
      {{ trans("lang.coupon_expires_at_help") }}
    </div>
  </div>
</div>

<!-- 'Boolean Enabled Field' -->
<div class="form-group row">
  {!! Form::label('enabled', trans("lang.coupon_enabled"),['class' => 'col-3 control-label text-right']) !!}
  {!! Form::hidden('enabled', 0, ['id'=>"hidden_enabled"]) !!}
  <div class="col-9 icheck-{{setting('theme_color')}}">
      {!! Form::checkbox('enabled', 1, null) !!}
      <label for="enabled"></label>
  </div>
</div>

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
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.coupon')}}</button>
  <a href="{!! route('coupons.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
