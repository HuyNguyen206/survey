<!----------------------------alert-------------------------------->
<?php
if (session('status') !== null){
	if (session('status')) {
		?>
		<div class="alert alert-block alert-success">
			<p><strong><i class="icon-ok"></i>
				{{trans('common.Success')}}!
				</strong>
				{{trans($controller.'.Create successful')}}
			</p>
		</div>
		<?php
	} else {
		?>
		<div class="alert alert-danger">
			<strong>
				<i class="icon-remove"></i>
				{{trans('common.Fail')}}!
			</strong>
			{{trans($controller.'.Create failed')}}
			<br />
		</div>
		<?php
	}
}
if (session('alert') !== null){
	?>
		<div class="alert alert-warning">
			<strong>
				<i class="icon-warning-sign"></i>
			{{trans('common.Warning')}}!
			</strong>
			{{trans(session('alert'))}}
			<br />
		</div>
	<?php
}

if (session('success') !== null){
	?>
		<div class="alert alert-success">
			<strong>
				<i class="icon-warning-sign"></i>
			{{trans('common.Success')}}!
			</strong>
			{{trans(session('success'))}}
			<br />
		</div>
	<?php
}

if (session('fail') !== null){
	?>
		<div class="alert alert-danger">
			<strong>
				<i class="icon-remove"></i>
				{{trans('common.Fail')}}!
			</strong>
			{{trans(session('fail'))}}
			<br />
		</div>
	<?php
}

if (session('del') !== null){
	?>
		<div class="alert alert-success">
			<p><strong><i class="icon-ok"></i>
				{{trans('common.Success')}}!
				</strong>
				{{trans($controller.'.Deleted successfully')}}
			</p>
		</div>
	<?php
}
?>



<div id='action_process_alert' class="alert alert-warning" style="display:none;">
	<strong>
		<i class="icon-warning-sign"></i>
		{{trans('common.Warning')}}!
	</strong>
	<c id="action_process_alert_message"></c>
	<br />
</div>

<div id='action_process_success' class="alert alert-success" style="display:none;">
	<strong>
		<i class="icon-ok"></i>
		{{trans('common.Success')}}!
	</strong>
	<c id="action_process_success_message"></c>
	<br />
</div>

<div id='action_process_fail' class="alert alert-danger" style="display: none;">
	<strong>
		<i class="icon-remove"></i>
		{{trans('common.Fail')}}!
	</strong>
	<c id="action_process_fail_message"></c>
	<br />
</div>
<!----------------------------end alert-------------------------------->