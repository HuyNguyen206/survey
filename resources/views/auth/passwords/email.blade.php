@extends('layouts.appLogin')

<!-- Main Content -->
@section('content')
<form class="form-signin" role="form" method="POST" action="{{ url('/password/email') }}">
	<h2 class="form-signin-heading"><?php echo trans('login.Reset Password'); ?></h2>
	<div class="login-wrap">
		{!! csrf_field() !!}
		
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
		@endif
		
		<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
			<div class="">
				<input required type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="<?php echo trans('login.Email'); ?>" oninvalid="InvalidMsg(this)" oninput="InvalidMsg(this)" />

				@if ($errors->has('email'))
					<span class="help-block">
						<strong>{{ $errors->first('email') }}</strong>
					</span>
				@endif
			</div>
		</div>

		<div class="form-group">
			<button class="btn btn-lg btn-login btn-block" type="submit"><?php echo trans('login.Send Link');?></button>
		</div>
		<div class="registration">
			<?php echo trans("login.Don't have an account yet");?>?
			<a class="" href="{{ url('/register') }}">
				<?php echo trans('login.Create an account');?>
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
