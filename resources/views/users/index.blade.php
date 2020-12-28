@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'users'; 
		$title = 'List users';
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
						<table id="sample-table-1" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>{{trans($transfile.'.Name')}}</th>
									<th>{{trans($transfile.'.Email')}}</th>
									<th>
										<i class="icon-time bigger-110 hidden-480"></i>
										{{trans($common.'.Create at')}}
									</th>
									<th>
										<i class="icon-time bigger-110 hidden-480"></i>
										{{trans($common.'.Update at')}}
									</th>

									<th></th>
								</tr>
							</thead>

							<tbody>
								@foreach($users as $user)
								<tr>
									<td>
										<strong>{{$user->name}}</strong>
									</td>
									<td>{{$user->email}}</td>
									<td>{{$user->created_at}}</td>
									<td>{{$user->updated_at}}</td>

									<td>
										<div class="visible-md visible-lg hidden-sm hidden-xs btn-group">
											<button class="btn btn-xs btn-success">
												<i class="icon-ok bigger-120"></i>
											</button>

											<button class="btn btn-xs btn-info">
												<i class="icon-edit bigger-120"></i>
											</button>

											<button class="btn btn-xs btn-danger">
												<i class="icon-trash bigger-120"></i>
											</button>

											<button class="btn btn-xs btn-warning">
												<i class="icon-flag bigger-120"></i>
											</button>
										</div>

										<div class="visible-xs visible-sm hidden-md hidden-lg">
											<div class="inline position-relative">
												<button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
													<i class="icon-cog icon-only bigger-110"></i>
												</button>

												<ul class="dropdown-menu dropdown-only-icon dropdown-yellow pull-right dropdown-caret dropdown-close">
													<li>
														<a href="#" class="tooltip-info" data-rel="tooltip" title="View">
															<span class="blue">
																<i class="icon-zoom-in bigger-120"></i>
															</span>
														</a>
													</li>

													<li>
														<a href="#" class="tooltip-success" data-rel="tooltip" title="Edit">
															<span class="green">
																<i class="icon-edit bigger-120"></i>
															</span>
														</a>
													</li>

													<li>
														<a href="#" class="tooltip-error" data-rel="tooltip" title="Delete">
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
@stop