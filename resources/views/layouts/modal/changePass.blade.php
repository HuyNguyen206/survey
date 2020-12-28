<div id="dialog-changepass-confirm" class="hide">
	<form class="form-horizontal" role="form">
		<div class="space-4"></div>
		<div class="space-4"></div>
		{!! csrf_field() !!}
		
		@include('layouts.modal.alert')
		<div class="form-group">
			<div class="col-sm-12">
				<input id='change_oldpassword' type="password" class="col-xs-12 col-sm-12" name="oldpassword" placeholder="{{trans('users.Old password')}}">
			</div>
		</div>

		<div class="space-4"></div>
		
		<div class="form-group">
			<div class="col-sm-12">
				<input id='change_newpassword' type="password" class="col-xs-12 col-sm-12" name="newpassword" placeholder="{{trans('users.New password')}}">
			</div>
		</div>

		<div class="space-4"></div>

		<div class="form-group">

			<div class="col-sm-12">
				<input id='change_confirmpassword' type="password" class="col-xs-12 col-sm-12" name="password_confirmation" placeholder="{{trans('users.Confirm password')}}">
			</div>
		</div>

	</form>
</div>