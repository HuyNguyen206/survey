@extends('layouts.app')

@section('content')

<div class="page-content">
	<?php 
		$controller = 'roles'; 
		$title = 'Create new role';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->

	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->

			<form class="form-horizontal" role="form" method="POST" action="{{ url(main_prefix.'/'.$controller) }}">
				{!! csrf_field() !!}
				
				@include('layouts.alert')
				
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="name"> {{trans($transfile.'.Name')}}:</label>

					<div class="col-sm-9">
						<input type="text" class="col-xs-12 col-sm-5" name="name" value="{{ old('name') }}" placeholder="{{trans($transfile.'.Input here')}}" oninvalid="this.setCustomValidity('{{trans($transfile.'.require name')}}')" oninput="setCustomValidity('')">
					
						@if ($errors->has('name'))
						<span class="col-xs-12 col-sm-12 no-padding-left red">
							<strong>{{ $errors->first('name') }}</strong>
						</span>
						@endif	
					</div>
					
					
				</div>
				
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="description"> {{trans($transfile.'.Description')}}: </label>

					<div class="col-sm-9">
						<input type="text" class="col-xs-12 col-sm-5" name="description" value="{{ old('description') }}" placeholder="{{trans($transfile.'.Input here')}}">
						<span class="help-inline col-xs-12 col-sm-7">
							<span class="middle">{{trans($common.'.Can leave empty')}}</span>
						</span>
						
						@if ($errors->has('description'))
						<span class="col-xs-12 col-sm-12 no-padding-left red">
							<strong>{{ $errors->first('description') }}</strong>
						</span>
						@endif
					</div>
				</div>

				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="level"> Cấp độ: </label>
					
					<div class="col-sm-9">
						<div class="col-xs-12 col-sm-5 no-padding">
							<select name='level' class="col-xs-12 no-padding-left" id="form-field-select-1">
								@foreach($data as $role)
								@if(old('level') == $role->level)
									<option selected="selected" value="{{ $role->level }}">{{$role->level.' - '. $role->display_name }}</option>
								@else 
									<option value="{{ $role->level }}">{{$role->level.' - '.$role->display_name }}</option>
								@endif
								@endforeach
							</select>
						</div>
						<span class="col-xs-12 col-sm-7">
							<input type="radio" name="rate" checked value="0">
							<label for="rate">Thấp</label>
							<input type="radio" name="rate" @if(old('rate') == "1") checked  @endif value="1">
							<label for="rate">Ngang</label>
						</span>
						@if ($errors->has('level'))
						<span class="col-xs-12 col-sm-12 no-padding-left red">
							<strong>{{ $errors->first('level') }}</strong>
						</span>
						@endif
					</div>
				</div>
				
				<div class="clearfix form-actions">
					<div class="col-md-offset-3 col-md-9">
						<button class="btn btn-info" type="submit">
							<i class="icon-ok bigger-110"></i>
							{{trans($common.'.Create')}}
						</button>

						&nbsp; &nbsp; &nbsp;
						<button class="btn" type="reset">
							<i class="icon-undo bigger-110"></i>
							{{trans($common.'.Reset')}}
						</button>
					</div>
				</div>
			</form>

			<!-- PAGE CONTENT ENDS -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</div><!-- /.page-content -->


@endsection