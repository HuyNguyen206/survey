@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'permissions';
		$title = 'List permissions';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->

	<div class="row">
		<div class="col-xs-12">
			<!-- PAGE CONTENT BEGINS -->

			<div class="row">
				<div class="col-xs-12">
					<div class="table-responsive">
						<table id="sample-table-2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th class="center">{{trans($common.'.Number')}}</th>
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
								<?php $i = 1; ?>
								@foreach($data as $role)
								<tr>
									<td class="center">{{$i}}</td>
									<td>
										<strong>{{$role->display_name}}</strong>
									</td>
									<td>{{$role->description}}</td>
									<td>{{$role->created_at}}</td>
									<td>{{$role->updated_at}}</td>
								</tr>
								<?php $i++; ?>
								@endforeach
							</tbody>
						</table>
					</div>
				</div><!-- /span -->
			</div><!-- /row -->
			
			<!-- PAGE CONTENT ENDS -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</div><!-- /.page-content -->

<script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.bootstrap.js')}}"></script>
@include('help.TranslateDatatableNotCheck')
@stop