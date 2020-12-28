@extends('layouts.app')

@section('content')

<div class="page-content">
	<?php 
		$controller = 'users'; 
		$title = 'Create new user';
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
					<label class="col-sm-3 control-label no-padding-right" for="email"> {{trans($transfile.'.Email')}}: </label>

					<div class="col-sm-9">
						<input type="email" class="col-xs-10 col-sm-5" name="email" value="{{ old('email') }}" placeholder="{{trans($transfile.'.Input here')}}" oninvalid="InvalidMsg(this)" oninput="InvalidMsg(this)">
						
						@if ($errors->has('email'))
						<span class="col-xs-12 col-sm-12 no-padding-left">
							<strong>{{ $errors->first('email') }}</strong>
						</span>
						@endif
					</div>
				</div>

				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="password"> {{trans($transfile.'.Password')}}: </label>

					<div class="col-sm-9">
						<input type="password" class="col-xs-10 col-sm-5" name="password" value="{{ old('password') }}" placeholder="{{trans($transfile.'.Input here')}}" oninvalid="this.setCustomValidity('<?php echo trans($transfile.'.require password'); ?>')" oninput="setCustomValidity('')">
						
						@if ($errors->has('password'))
						<span class="col-xs-12 col-sm-12 no-padding-left">
							<strong>{{ $errors->first('password') }}</strong>
						</span>
						@endif
					</div>
				</div>
				
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="password_confirmation"> {{trans($transfile.'.Confirm password')}}: </label>

					<div class="col-sm-9">
						<input type="password" class="col-xs-10 col-sm-5" name="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="{{trans($transfile.'.Input here')}}" oninvalid="this.setCustomValidity('<?php echo trans($transfile.'.require password'); ?>')" oninput="setCustomValidity('')">
						
						@if ($errors->has('password_confirmation'))
						<span class="col-xs-12 col-sm-12 no-padding-left">
							<strong>{{ $errors->first('password_confirmation') }}</strong>
						</span>
						@endif
					</div>
				</div>
				
				<div class="space-4"></div>

				<div class="form-group">
					<label class="col-sm-3 control-label no-padding-right" for="role"> {{trans($transfile.'.Role')}}: </label>

					<div class="col-sm-9">
						<div>
							<select name='role' class="col-xs-10 col-sm-5 no-padding-left" id="form-field-select-1">
								@foreach($roles as $role)
								@if(old('role') == $role->id)
								<option selected="selected" value="{{$role->id}}">{{$role->display_name}}</option>
								@else
								<option value="{{$role->id}}">{{$role->display_name}}</option>
								@endif
								
								@endforeach
							</select>
						</div>
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

<script>
function InvalidMsg(textbox) {
    
    if (textbox.value == '') {
        textbox.setCustomValidity('<?php echo trans($common.'.fill email'); ?>');
    }
    else if(textbox.validity.typeMismatch){
        textbox.setCustomValidity('<?php echo trans($common.'.valid email'); ?>');
    }
    else {
        textbox.setCustomValidity('');
    }
    return true;
}
</script>

@endsection