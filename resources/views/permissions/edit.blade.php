@extends('layouts.app')
 
@section('content')
<div>Thay đổi quyền hạn: {!! $role->id !!}</div>
{!! Form::model($role, ['method' => 'PATCH', 'action' => ['RolesController@update', $role->id] ]) !!}
	{!! Form::label('name','Quyền hạn:') !!}
	{!! Form::text('name') !!}<br />
	{!! Form::label('created_at','Ngày tạo:') !!}
	{!! Form::input('date', 'created_at') !!} <br />
	{!! Form::submit('Cập nhật')!!}
{!! Form::close() !!}

@if ($errors->any())
	<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>	
@endif
@stop