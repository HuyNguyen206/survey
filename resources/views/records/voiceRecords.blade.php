@extends('layouts.app')

@section('content')
<div class="page-content">
	<?php 
		$controller = 'voicerecords';
		$title = 'List voice records';
		$transfile = $controller;
		$common = 'common';
	?>
	@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])
	<!-- /.page-header -->

	<div class="row">
		<div class="col-xs-12">
			@if(count($errors->all()))
				<div class="errorHandler alert alert-danger no-display">
					<i class="icon-remove-sign"></i>  
					@foreach ($errors->all() as $error)
						{{ $error }}
						<br/>
					@endforeach
				</div>
			@endif
			
			@include('layouts.alert')
			<!-- PAGE CONTENT BEGINS -->
			<form class="form-horizontal" role="form" method="POST" action="{{ url(main_prefix.'/search-voice-records')}}">
				<div class="row" id='advance_search'>
					<div class="col-xs-12">
						<div class="row">
							<div class="col-xs-4">
								<label for="phone">Số điện thoại</label>
								<input type="text" name="phone" class="form-control" 
									maxlength="200" value="{{$input['phone']}}">
							</div>
							<div class="col-xs-4">
								<label for="time_from">Thời gian bắt đầu</label>
								<div class="input-group">
									<?php $dateStart = date_format(date_create($input['time_from']), 'd-m-Y H:i:s'); ?>
									<input type="text" name="time_from"  value="{{$dateStart}}" id="time_from" class="form-control" placeholder="VD:15-12-2016" > 
									<span class="input-group-addon"> <i class="icon-calendar"></i> </span>
								</div>
							</div>
							<div class="col-xs-4">
								<label for="time_to">Thời gian kết thúc</label>
								<div class="input-group">
									<?php $dateEnd = date_format(date_create($input['time_to']), 'd-m-Y H:i:s'); ?>
									<input type="text" name="time_to"  value="{{$dateEnd}}" id="time_to" class="form-control" placeholder="VD:15-12-2016" > 
									<span class="input-group-addon"> <i class="icon-calendar"></i> </span>
								</div>
							</div>
						</div>

						<div class="space-4"></div>

						<button class="btn" type='submit'><i class="icon-search bigger-110"></i>Tìm</button>
					</div>
				</div>
				<div class="space-4"></div>
				
				{!! csrf_field() !!}
				<div class="row">
					<div class="col-xs-12">
						<div class="row">
							<div class="col-xs-12">
								<div class="table-header">
									Danh sách file ghi âm
								</div>

								<div class="table-responsive">
									<table id="sample-table-2" class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th class="center">STT</th>
												<th class="width-35"><i class="icon-phone bigger-120">Số điện thoại</th>
												<th><i class="icon-time bigger-120"></i>Thời gian bắt đầu cuộc gọi</th>
												<th>Hành động</th>
											</tr>
										</thead>

										<tbody>
											
											<?php foreach($voice as $key => $val){?>
											<tr>
												<td class="center">{{$key + 1}}</td>
												<td>{{$val->called}}</td>
												<td>{{date('d-m-Y H:i:s',strtotime($val->calldate))}}</td>
												<td>
												<?php
													$date = date('Y-m-d/H/i',strtotime($val->calldate));
													$url = sprintf('http://118.69.241.36/media/%s/AUDIO/%s.mp3', $date, $val->fbasename);
												?>
													<a href="{{$url}}"><button type="button" class="btn btn-xs btn-success">
														<i class="icon-headphones bigger-120"></i>
														</button>
													</a>
												</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div><!-- /span -->
				</div><!-- /row -->
			</form>
			<!-- PAGE CONTENT ENDS -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</div><!-- /.page-content -->

<link type="text/css" href="{{asset('assets/css/datetimepicker.css')}}" rel="stylesheet" media="screen">

<script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.bootstrap.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-datetimepicker.js')}}"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('#time_from').datetimepicker({
			format: 'dd-mm-yyyy h:i:s',
			autoclose: true,
		});
		$('#time_to').datetimepicker({
			format: 'dd-mm-yyyy h:i:s',
			autoclose: true,
		});
	});
</script>

@stop