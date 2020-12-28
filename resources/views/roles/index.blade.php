@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'roles'; 
		$title = 'List roles';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->
	@include('layouts.modal.deleteConfirm')
	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->

			@include('layouts.alert')
			<div class="row">
				<div class="col-xs-12">
					<div class="table-responsive">
						<table id="sample-table-2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th class="center">{{trans($common.'.Number')}}</th>
									<th>{{trans($transfile.'.Name')}}</th>
									<th>{{trans($transfile.'.Description')}}</th>
									<th class='visible-md visible-lg hidden-sm hidden-xs'>
										<i class="icon-star-half-full bigger-110 hidden-480"></i>
										{{'Cấp độ'}}
									</th>
									<th>
										<i class="icon-time bigger-110 hidden-480"></i>
										{{trans($common.'.Create at')}}
									</th>
									

									<th>{{'Hành động'}}</th>
								</tr>
							</thead>

							<tbody>
								<?php $i = 1; ?>
								@foreach($data as $role)
								<tr>
									<td class="center">{{$i}}</td>
									<td>
										<strong>{{$role->name}}</strong>
									</td>
									<td>{{$role->description}}</td>
									<td class='visible-md visible-lg hidden-sm hidden-xs'>
										{{$role->level}}
									</td>
									<td>{{$role->created_at}}</td>
									

									<td>
										<div class="visible-md visible-lg hidden-sm hidden-xs btn-group">
											<a class="no-underline hidden">
												<button class="btn btn-xs btn-info">
													<i class="icon-edit bigger-120"></i>
												</button>
											</a>
											
											<a class="no-underline" onclick="DeleteConfirm('{{$role->id}}','{{$role->name}}')">
												<button id="gritter-center" class="btn btn-xs btn-danger">
													<i class="icon-trash bigger-120"></i>
												</button>
											</a>
										</div>

										<div class="visible-xs visible-sm hidden-md hidden-lg">
											<div class="inline position-relative">
												<button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
													<i class="icon-cog icon-only bigger-110"></i>
												</button>

												<ul class="dropdown-menu dropdown-only-icon dropdown-yellow pull-right dropdown-caret dropdown-close">
													<li>
														<a href="#" class="tooltip-info hidden" data-rel="tooltip" title="View">
															<span class="blue">
																<i class="icon-zoom-in bigger-120"></i>
															</span>
														</a>
													</li>

													<li>
														<a href="#" class="tooltip-success hidden" data-rel="tooltip" title="Edit">
															<span class="green">
																<i class="icon-edit bigger-120"></i>
															</span>
														</a>
													</li>

													<li>
														<a class="tooltip-error" data-rel="tooltip" title="Delete" onclick="DeleteConfirm('{{$role->id}}','{{$role->name}}')">
															<span class="red">
																<i class="icon-trash bigger-120"></i>
															</span>
														</a>
													</li>
												</ul>
											</div>
										</div>
									</td>
								</tr>
								<?php $i++; ?>
								@endforeach
							</tbody>
						</table>
					</div><!-- /.table-responsive -->
				</div><!-- /span -->
			</div><!-- /row -->
			
			<!-- PAGE CONTENT ENDS -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</div><!-- /.page-content -->

<script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.bootstrap.js')}}"></script>
<link rel="stylesheet" href="{{asset('assets/css/jquery-ui-1.10.3.full.min.css')}}" />
<script src="{{asset('assets/js/jquery-ui-1.10.3.full.min.js')}}"></script>
@include('help.TranslateDatatableNotCheck')

<script type="text/javascript">
	$.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
		_title: function(title) {
			var $title = this.options.title || '&nbsp;'
			if( ("title_html" in this.options) && this.options.title_html == true )
				title.html($title);
			else title.text($title);
		}
	}));
	
	function Delete(id){
		$('.alert').hide(300);
		$('#action_process_alert').hide(300);
		$('#action_process_success').hide(300);
		$('#action_process_fail').hide(300);
		$.ajax({
			url: '<?php echo url('/'.main_prefix.'/'.$controller).'/';?>' + id,
			cache: false,
			type: "delete",
			dataType: "json",
			data: {'_token':'<?php echo csrf_token(); ?>'},
			success: function (data) {
				if(data.state === 'alert'){
					$('#action_process_alert_message').html(data.error);
					$('#action_process_alert').show(300);
				}
				else if(data.state === 'success'){
					window.location='<?php echo url('/'.main_prefix.'/'.$controller);?>';
				}else{
					$('#action_process_fail_message').html(data.error);
					$('#action_process_fail').show(300);
				}
			},
			error: function(error){
				if(error.status === 200){
					location.href = '<?php echo url('/error/auth');?>';
				}
			},
		});
	}
	
	function DeleteConfirm(id, name){
		var mess = '<?php echo 'Vai trò';?><b class="red"> ' + name + ' </b><?php echo 'sẽ bị xóa và không thể khôi phục';?>.';
		$('#delete_confirm_message').html(mess);
		$( "#delete-confirm" ).removeClass('hide').dialog({
			resizable: false,
			modal: true,
			title: "<div class='widget-header'><h4 class='smaller'><i class='icon-warning-sign red'></i> <?php echo trans($transfile.'.You wanna delete role'); ?>?</h4></div>",
			title_html: true,
			buttons: [
				{
					html: "<i class='icon-ok bigger-110'></i>&nbsp; <?php echo trans('common.Delete'); ?>",
					"class" : "btn btn-danger btn-xs",
					click: function() {
						Delete(id);
						$( this ).dialog( "close" );
					}
				}
				,
				{
					html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
					"class" : "btn btn-xs",
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
	}
	
</script>

@stop