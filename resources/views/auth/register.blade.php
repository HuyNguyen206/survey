@extends('layouts.appLogin')

@section('content')
<form class="form-signin" role="form" method="POST" action="{{ url('/register') }}">
	<h2 class="form-signin-heading"><?php echo trans('login.Registration'); ?></h2>
	<div class="login-wrap">
		<p><?php echo trans('login.Enter your account details below'); ?></p>
		{!! csrf_field() !!}

		<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
			<div class="">
				<input required type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="<?php echo trans('login.Name'); ?>" oninvalid="this.setCustomValidity('<?php echo trans('login.require name'); ?>')" oninput="setCustomValidity('')" >

				@if ($errors->has('name'))
				<span class="help-block">
					<strong>{{ $errors->first('name') }}</strong>
				</span>
				@endif
			</div>
		</div>

		<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
			<div class="">
				<input required type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="<?php echo trans('login.Email'); ?>" oninvalid="InvalidMsg(this)" oninput="InvalidMsg(this)">

				@if ($errors->has('email'))
				<span class="help-block">
					<strong>{{ $errors->first('email') }}</strong>
				</span>
				@endif
			</div>
		</div>

		<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
			<div class="">
				<input required type="password" class="form-control" name="password" placeholder="<?php echo trans('login.Password'); ?>" oninvalid="this.setCustomValidity('<?php echo trans('login.require password'); ?>')" oninput="setCustomValidity('')">

				@if ($errors->has('password'))
				<span class="help-block">
					<strong>{{ $errors->first('password') }}</strong>
				</span>
				@endif
			</div>
		</div>

		<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
			<div class="">
				<input required type="password" class="form-control" name="password_confirmation" placeholder="<?php echo trans('login.Confirm password'); ?>" oninvalid="this.setCustomValidity('<?php echo trans('login.require confirm password'); ?>')" oninput="setCustomValidity('')">

				@if ($errors->has('password_confirmation'))
				<span class="help-block">
					<strong>{{ $errors->first('password_confirmation') }}</strong>
				</span>
				@endif
			</div>
		</div>

		<div class="form-group">
			<button class="btn btn-lg btn-login btn-block" type="submit"><?php echo trans('login.Register'); ?></button>
		</div>

		<div class="registration">
			<?php echo trans('login.Already Registered'); ?>.
			<a class="" href="{{ url('/login') }}">
				<?php echo trans('login.Login'); ?>
			</a>
		</div>

	</div>

</form>

<script>
function InvalidMsg(textbox) {
    
    if (textbox.value == '') {
        textbox.setCustomValidity('<?php echo trans('login.fill email'); ?>');
    }
    else if(textbox.validity.typeMismatch){
        textbox.setCustomValidity('<?php echo trans('login.valid email'); ?>');
    }
    else {
        textbox.setCustomValidity('');
    }
    return true;
}
</script>
@endsection
