@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'authens';
		$title = 'List roles - users';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->
	
	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->
			<form class="form-horizontal" role="form" method="POST" action="{{ url(main_prefix.'/'.$controller.'/view-role-user') }}">
			{!! csrf_field() !!}
			
			@include('layouts.alert')
			
			<div class="row">
				<div class="col-xs-12">
					<div class="row">
						<div class="col-xs-12">
							<div class="table-responsive">
								<table id="sample-table-2" class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											<th class="center">{{trans($common.'.Number')}}</th>
											<th>{{trans($transfile.'.Name')}}</th>
											<th>{{trans($transfile.'.Email')}}</th>
											<th>
												<i class="icon-key bigger-110 hidden-480"></i>
												{{trans($transfile.'.Roles')}}
											</th>
										</tr>
									</thead>

									<tbody>
										<?php $i = 1; ?>
										@foreach($userrole as $values)
										<tr>
											<td class="center">{{$i}}</td>
											<td><strong>{{$values->name}}</strong></td>
											<td><strong>{{$values->email}}</strong></td>
											<td>
												<select id="baserole_{{$values->id}}" name="baserole_{{$values->id}}">
													@foreach($roles as $role)
														<option <?php if($values->role_id == $role->id){ echo 'selected';} ?> value="{{$role->id}}">{{$role->display_name}}</option>
													@endforeach
												</select>
											
											</td>
										</tr>
										<?php $i++; ?>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="clearfix form-actions">
						<div class="col-md-offset-3 col-md-9">
							<button class="btn btn-info" type="submit">
								<i class="icon-ok bigger-110"></i>
								{{trans($common.'.Save')}}
							</button>

							&nbsp; &nbsp; &nbsp;
							<a href="{{ url(main_prefix.'/'.$controller.'/view-role-user') }}">
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
@include('help.TranslateDatatableNotCheck')
@stop