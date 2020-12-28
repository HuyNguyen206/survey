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
			<form class="form-horizontal" role="form" method="POST" action="{{ url('/'.$controller.'/process') }}">
				{!! csrf_field() !!}
			<div class="row">
				<div class="col-xs-12">
					<div class="">
						<label style="padding-top: 7px;" for="form-field-select-3">{{trans('roles.Roles')}}:</label>
						<!--<br />-->
						<select class="width-25 chosen-select" id="">
							<option value=""><?php echo trans($common.".placeholder_text_chosen_box"); ?></option>
							@foreach($roles as $role)
							<option value="{{$role->id}}">{{$role->display_name}}</option>
							@endforeach
						</select>
					</div>
					
					<div class="space-4"></div>
					
					<div class="row">
						<div class="col-xs-12">
							<h3 class="header smaller lighter blue">jQuery dataTables</h3>
							<div class="table-header">
								Results for "Latest Registered Domains"
							</div>

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
													<input type="checkbox" class="ace" />
													<span class="lbl"></span>
												</label>
											</td>

											<td><strong>{{$permission->name}}</strong></td>
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

					
					<div class="table-responsive">
						<table id="sample-table-1" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
<!--									<th class="center">
										<label>
											<input type="checkbox" class="ace" />
											<span class="lbl"></span>
										</label>
									</th>-->
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
<!--									<td class="center">
										<label>
											<input type="checkbox" class="ace" <?php
												foreach($perrol as $value){
													
												}
											?>/>
											<span class="lbl"></span>
										</label>
									</td>-->
									<td>
										<strong>{{$permission->name}}</strong>
									</td>
									<td>{{$permission->description}}</td>
									<td>{{$permission->created_at}}</td>
									<td>{{$permission->updated_at}}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div><!-- /.table-responsive -->
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

<script type="text/javascript">
	jQuery(function($) {
		$(".chosen-select").chosen({
			no_results_text: "<?php echo trans($common.".no_results_text_chosen_box"); ?>"
		}); 
		
		var oTable1 = $('#sample-table-2').dataTable( {
		"aoColumns": [
		  { "bSortable": false },null, null,null,null
		],
		"bJQueryUI":false,
		"olanguage":{
				"sLengthMenu": "Display _MENU_ records per page",
				"sZeroRecords": "Không tìm thấy - sorry",
				"sInfo": "Có _START_ tới _END_ của _TOTAL_ bản ghi",
				"sInfoEmpty": "Có 0 tới 0 của 0 bản ghi",
				"sInfoFiltered": "(filtered from _MAX_ total records)"
			}
	});

		$('table th input:checkbox').on('click' , function(){
			var that = this;
			$(this).closest('table').find('tr > td:first-child input:checkbox')
			.each(function(){
				this.checked = that.checked;
				$(this).closest('tr').toggleClass('selected');
			});
		});


		$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
		function tooltip_placement(context, source) {
			var $source = $(source);
			var $parent = $source.closest('table')
			var off1 = $parent.offset();
			var w1 = $parent.width();

			var off2 = $source.offset();
			var w2 = $source.width();

			if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
			return 'left';
		}
	});
</script>

@stop