@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'authens';
		$title = 'List users - permission';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->
	
	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->
			<form class="form-horizontal" role="form" method="POST" action="{{ url(main_prefix.'/'.$controller.'/view-user-permission') }}">
			{!! csrf_field() !!}
			
			@include('layouts.alert')
			
			<div class="row">
				<div class="col-xs-12">
					<div class="row">
						<div class="col-xs-12">
							
							<div class="">
								<label style="padding-top: 7px;" for="form-field-select-3">{{trans('users.Users')}}:</label>
								<!--<br />-->
								<select class="width-25 chosen-select" id="baseuser" name="baseuser" onchange="changeUser()">
									@foreach($userrole as $user)
									<option @if(session('olduser') == $user->id) selected @endif value="{{$user->id}}">{{$user->name.' - '. $user->email}}</option>
									@endforeach
								</select>

							</div>
							
							<div class="space-4"></div>
							@foreach($zone as $key => $val)
							<div class="col-xs-6">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											<th colspan="3" class='center'><h4>{{$val['zone_name']}}<h4></th>
										</tr>
										<tr>
											<th class="center">
												<label>
													<input id='zone-{{$val['zone_id']}}' type="checkbox" class="ace" />
													<span class="lbl"></span>
												</label>
											</th>
											<th class="center">STT</th>
											<th>Chi nhánh</th>
										</tr>
									</thead>

									<tbody>
										<?php $i = 1; ?>
										@foreach($brand as $values)
										@if($values['region'] === $val['zone_name'])
										<tr>
											<td class="center">
												<label>
													<input type="checkbox" class="ace" id="{{'area-'.$values['id']}}" name="zone_{{$val['zone_id']}}[]" value="{{$values['id']}}"/>
													<span class="lbl"></span>
												</label>
											</td>
											<td class="center">{{$i}}</td>
											<td><strong>{{$values['name']}}</strong></td>
										</tr>
										<?php $i++; ?>
										@endif
										@endforeach
									</tbody>
								</table>
							</div>
							
<!--							@if(($key+1) % 3 == 0)
							<div class="col-xs-12">
								abc
							</div>
							@endif-->
							
							@endforeach
							
						</div>
					</div>
					
					<div class="clearfix form-actions">
						<div class="col-md-offset-3 col-md-9">
							<button class="btn btn-info" type="submit">
								<i class="icon-ok bigger-110"></i>
								{{trans($common.'.Save')}}
							</button>

							&nbsp; &nbsp; &nbsp;
							<a href="{{ url(main_prefix.'/'.$controller.'/view-user-permission') }}">
							<button class="btn" type="button">
								<i class="icon-undo bigger-110"></i>
								{{trans($common.'.Reset')}}
							</button>
							</a>
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
	
	function changeUser() {
		var user = $('#baseuser').val();
		var brand = [<?php foreach ($brand as $val) {echo '"area-' . $val['id'] . '",';} ?>];
		var zone = [<?php foreach ($zone as $val) {echo '"zone-' . $val['zone_id'] . '",';} ?>];
		var userarea = [<?php foreach ($userrole as $val) {
			if(!empty($val->user_brand)){
				$brands = json_decode($val->user_brand);
				foreach($brands as $brand){
					echo '"'.$val->id.'@area-' . $brand . '",';
				}
			}
		} ?>];		
		
		var userzone = [<?php foreach ($userrole as $val) {
			if(!empty($val->user_zone)){
				$brands = json_decode($val->user_zone);
				foreach($brands as $brand){
					echo '"'.$val->id.'@zone-' . $brand . '",';
				}
			}
		} ?>];
		
		for (i = 0; i < brand.length; i++) {
			$("#" + brand[i]).removeAttr('checked');
		}
		
		for (i = 0; i < zone.length; i++) {
			$("#" + zone[i]).removeAttr('checked');
		}
		
		for (i = 0; i < userzone.length; i++) {
			var temp = userzone[i].split('@');
			if(temp[0] === user){
				$("#" + temp[1]).prop('checked', 'checked');
			}
		}
		
		for (i = 0; i < userarea.length; i++) {
			var temp = userarea[i].split('@');
			if(temp[0] === user){
				$("#" + temp[1]).prop('checked', 'checked');
			}
		}
		
		
	}
	
    $(document).ready(function () {
		changeUser();
	});

</script>

@stop