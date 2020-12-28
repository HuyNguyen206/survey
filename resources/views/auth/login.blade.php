@extends('layouts.appLogin')

@section('content')
<div class="main-content">
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="login-container">
				<div class="center">
					<h1>
						<i class="icon-leaf green"></i>
						<span class="red">CEM</span>
						<span class="white">Survey</span>
						<span class="orange">OpenNet</span>
					</h1>
					<h4 class="blue">&copy; ISC - VAS</h4>
				</div>

				<div class="space-6"></div>

				<div class="position-relative">
					<div id="login-box" class="login-box visible widget-box no-border">
						<div class="widget-body">
							<div class="widget-main">
								<h4 class="header blue lighter bigger">
									<i class="icon-coffee green"></i>
									{{trans('login.Please Enter Your Information')}}
								</h4>

								<div class="space-6"></div>

								<form role="form" method="POST" action="{{ url('/login') }}">
									{!! csrf_field() !!}
									<fieldset>
										<label class="block clearfix">
											<span class="block input-icon input-icon-right">
                                                <input type="text" class="form-control" name="name" value="{{old('name')}}" placeholder="{{trans('login.Account')}}"/>
												<i class="icon-user"></i>
											</span>
											
											@if ($errors->has('name'))
												<span class="help-block">
													<strong>{{ $errors->first('name') }}</strong>
												</span>
											@endif
										</label>

										<label class="block clearfix">
											<span class="block input-icon input-icon-right">
												<input type="password" class="form-control" name="password" placeholder="{{trans('login.Password')}}"/>
												<i class="icon-lock"></i>
											</span>
											@if ($errors->has('password'))
												<span class="help-block">
													<strong>{{ $errors->first('password') }}</strong>
												</span>
											@endif
										</label>

										<label class="block clearfix">
											<span class="block input-icon input-icon-right">
												<input id="useOTP" type="checkbox" class="ace" name="useOTP" onclick="checkOTP(this)"/>
												<span class="lbl"> {{trans('login.UseOTP')}}</span>
											</span>
										</label>

										<label id="otpDiv" style="display: none;">
											<span class="block input-icon input-icon-right">
												<input type="text" class="form-control" name="otp" placeholder="{{trans('login.OTP')}}"/>
												<i class="icon-lock"></i>
											</span>
											@if ($errors->has('otp'))
												<span class="help-block">
													<strong>{{ $errors->first('otp')}}</strong>
												</span>
											@endif
										</label>

										<div class="space"></div>

										<div class="clearfix">
											<button type="submit" class="width-35 pull-right btn btn-sm btn-primary">
												<i class="icon-key"></i>
												{{trans('login.Login')}}
											</button>
										</div>

										<div class="space-4"></div>
									</fieldset>
								</form>
								
							</div><!-- /widget-main -->
						</div><!-- /widget-body -->
					</div><!-- /login-box -->
				</div><!-- /position-relative -->
			</div>
		</div><!-- /.col -->
	</div><!-- /.row -->
</div>

<script type="text/javascript">
	$(document).ready(function () {
		checkOTP($("#useOTP"));
	});

function checkOTP(checkbox){
	if (checkbox.checked)
	{
		$("#otpDiv").addClass('block clearfix').show();
	}else{
		$("#otpDiv").removeClass('block clearfix').hide();
	}
}
</script>

@endsection
