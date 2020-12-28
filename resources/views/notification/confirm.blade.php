@extends('layouts.appBlank')

@section('content')
<div class="page-content">
	<div class="row">
		<!-- PAGE CONTENT BEGINS -->
		 <div class="widget-box" style='width: 600px; margin: auto;'>
			<div class="widget-header widget-header-flat lighter smaller blue center">
				<h4>Xác nhận thông báo khảo sát</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main">
					<div class="">
						<form class="form-horizontal" role="form" method="POST" action="<?php echo url('confirm');?>">
							{!! csrf_field() !!}
							<div class="form-group">
								<div class='col-xs-2'></div>
								<div class="col-xs-10">
									<?php echo html_entity_decode($mail); ?>
								</div>
							</div>
							
							<div class="clearfix form-actions">
								@include('layouts.modal.alertNotTrans')
							<?php if(isset($queue->confirm_user)){?>
								<div class="col-xs-12">
									Đã được xác nhận bởi {{$queue->confirm_user}}</br>
								</div>
							<?php }else{ ?>
								<div class="col-xs-12">
									Chưa được xác nhận</br>
								</div>
							<?php } ?>
							</div>
								
							<?php if($confirm){ ?>
							<input type='hidden' name='code' value='{{$code}}'/>
							<input type='hidden' name='note' value='Not use now' />
							<div class="clearfix form-actions">
								<div class="center">
									<button class="btn btn-info" type="submit">
										<i class="icon-ok bigger-110"></i>
										Xác nhận
									</button>
								</div>
							</div>
							<?php } ?>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- PAGE CONTENT ENDS -->
	</div><!-- /.row -->
</div><!-- /.page-content -->

@stop