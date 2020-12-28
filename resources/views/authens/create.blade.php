@extends('layouts.app')

@section('content')

<div class="page-content">
	<?php 
		$controller = 'permissions'; 
		$title = 'Create new permission';
		$transfile = $controller;
		$common = 'common';
        $prefix = 'dashboard';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->

	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->

			<form class="form-horizontal" role="form" method="POST" action="{{ url($prefix.'/'.$controller.'/process') }}">
				{!! csrf_field() !!}
				
				@include('layouts.alert')
				
				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="name"> {{trans($transfile.'.Name')}}:</label>

					<div class="col-sm-9">
						<input type="text" class="col-xs-10 col-sm-5" name="name" value="{{ old('name') }}" placeholder="{{trans($transfile.'.Input here')}}" oninvalid="this.setCustomValidity('{{trans($transfile.'.require name')}}')" oninput="setCustomValidity('')">
					
						@if ($errors->has('name'))
						<span class="col-xs-12 col-sm-12 no-padding-left">
							<strong>{{ $errors->first('name') }}</strong>
						</span>
						@endif	
					</div>
					
					
				</div>
				
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="description"> {{trans($transfile.'.Description')}}: </label>

					<div class="col-sm-9">
						<input type="text" class="col-xs-10 col-sm-5" name="description" value="{{ old('description') }}" placeholder="{{trans($transfile.'.Input here')}}">
						<span class="help-inline col-xs-12 col-sm-7">
							<span class="middle">{{trans($common.'.Can leave empty')}}</span>
						</span>
						
						@if ($errors->has('description'))
						<span class="col-xs-12 col-sm-12 no-padding-left">
							<strong>{{ $errors->first('description') }}</strong>
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