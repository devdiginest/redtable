<!-- Id Field -->
<div class="form-group row col-6">
  {!! Form::label('id', 'Id:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->id !!}</p>
  </div>
</div>

<!-- Name Field -->
<div class="form-group row col-6">
  {!! Form::label('name', 'Name:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->name !!}</p>
  </div>
</div>

<!-- Description Field -->
<div class="form-group row col-6">
  {!! Form::label('description', 'Description:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->description !!}</p>
  </div>
</div>

<!-- Image Field -->
<div class="form-group row col-6">
  {!! Form::label('image', 'Image:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->image !!}</p>
  </div>
</div>

<!-- Address Field -->
<div class="form-group row col-6">
  {!! Form::label('address', 'Address:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->address !!}</p>
  </div>
</div>

<!-- Latitude Field -->
<div class="form-group row col-6">
  {!! Form::label('latitude', 'Latitude:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->latitude !!}</p>
  </div>
</div>

<!-- Longitude Field -->
<div class="form-group row col-6">
  {!! Form::label('longitude', 'Longitude:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->longitude !!}</p>
  </div>
</div>

<!-- Phone Field -->
<div class="form-group row col-6">
  {!! Form::label('phone', 'Phone:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->phone !!}</p>
  </div>
</div>

<!-- Email Field -->
<div class="form-group row col-6">
  {!! Form::label('email', 'Email:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->email !!}</p>
  </div>
</div>

<!-- Working Hours Field -->
<div class="form-group row col-6">
  {!! Form::label('working_hours', 'Working Hours:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->working_hours !!}</p>
  </div>
</div>

<!-- Created At Field -->
<div class="form-group row col-6">
  {!! Form::label('created_at', 'Created At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->created_at !!}</p>
  </div>
</div>

<!-- Updated At Field -->
<div class="form-group row col-6">
  {!! Form::label('updated_at', 'Updated At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $restaurant->updated_at !!}</p>
  </div>
</div>

