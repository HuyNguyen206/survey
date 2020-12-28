@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'authens';
		$title = 'List roles - permissions';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->
	
	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->
			<form class="form-horizontal" role="form" method="POST" action="{{ url(main_prefix.'/'.$controller.'/view-role-permission') }}">
			{!! csrf_field() !!}
			
			@include('layouts.alert')
			
			<div class="row">
				<div class="col-xs-12">
					<div class="">
						<label style="padding-top: 7px;" for="form-field-select-3">{{trans('roles.Roles')}}:</label>
						<!--<br />-->
						<select class="width-25 chosen-select" id="baserole" name="baserole" onchange="changeRole()">
							@foreach($roles as $role)
							<option <?php if(session('oldbaserole') == $role->id){ echo 'selected';} ?> value="{{$role->id}}">{{$role->display_name}}</option>
							@endforeach
						</select>
						
					</div>
					
					<div class="space-4"></div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="table-responsive">
								<table id="sample-table-2" class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											<th class="center">
												<label>
													<input type="checkbox" class="ace" />
													<span class="lbl"></span>
												</label>
											</th>
											<th>{{trans($transfile.'.Name')}}</th>
											<th>{{trans($transfile.'.Description')}}</th>
											<th>
												<i class="icon-time bigger-110 hidden-480"></i>
												{{trans($common.'.Create at')}}
											</th>
											<th>
												<i class="icon-time bigger-110 hidden-480"></i>
												{{trans($common.'.Update at')}}
											</th>

										</tr>
									</thead>

									<tbody>
										@foreach($permissions as $permission)
										<tr>
											<td class="center">
												<label>
													<input type="checkbox" class="ace" id="{{$permission->name}}" name="Base[]" value="{{$permission->id}}"/>
													<span class="lbl"></span>
												</label>
											</td>

											<td><strong>{{$permission->display_name}}</strong></td>
											<td>{{$permission->description}}</td>
											<td class="hidden-480">{{$permission->created_at}}</td>
											<td class="hidden-480">{{$permission->updated_at}}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="clearfix form-actions">
						<div>{{trans($common.'.Note: if you search before save, please empty search field')}}!</div>
						
						<div class="space-4"></div>
						
						<div class="col-md-offset-3 col-md-9">
							<button class="btn btn-info" type="submit" id="btn_submit">
								<i class="icon-ok bigger-110"></i>
								{{trans($common.'.Save')}}
							</button>

							&nbsp; &nbsp; &nbsp;
							<button class="btn" type="button" onclick="changeRole()">
								<i class="icon-undo bigger-110"></i>
								{{trans($common.'.Reset')}}
							</button>
						</div>
					</div>
				</div><!-- /span -->
			</div><!-- /row -->
			
			</form>
			<!-- PAGE CONTENT ENDS -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</div><!-- /.page-content -->

<link rel="stylesheet" href="{{asset('assets/css/chosen.min.css')}}" />

<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.bootstrap.js')}}"></script>
@include('help.TranslateDatatableFullView')

<script type="text/javascript">
	jQuery(function($) {
		$(".chosen-select").chosen({
			no_results_text: "<?php echo trans($common.".no_results_text_chosen_box"); ?>"
		}); 
	});
	
	function changeRole() {
		var role = $('#baserole').val();
		var permission = [<?php foreach ($permissions as $val) {echo '"' . $val->name . '",';} ?>];
		var perrol = [<?php foreach ($perrol as $val) {echo '"' . $val->role_id . '@' . $val->name . '",';} ?>];
		for (i = 0; i < permission.length; i++) {
			$("#" + permission[i]).removeAttr('checked');
		}
		
		for (i = 0; i < perrol.length; i++) {
			var temp = perrol[i].split('@');
			if(temp[0] === role){
				$("#" + temp[1]).prop('checked', 'checked');
			}
		}
	}
	
	$('#btn_submit').click(function(e){
		if($('#sample-table-2_filter').children().children().val() !== ''){
			e.preventDefault();
		}
	});
	
    $(document).ready(function () {
		changeRole();
	});
</script>

@stop