<?php $prefix = 'dashboard';
if (session('main_breadcrumb') !== null) {
    $bread = session('main_breadcrumb');
    if(session('main_breadcrumb') === 'DashboardController'){
        $bread = 'Dashboard';
        $prefix = '';
    }
	?>
	<div class="breadcrumbs" id="breadcrumbs">
		<script type="text/javascript">
	        try {
	            ace.settings.check('breadcrumbs', 'fixed')
	        } catch (e) {
	        }
		</script>

		<ul class="breadcrumb">
			<li>
				<i class="icon-home home-icon"></i>
				<a href="{{url($prefix.'/'.strtolower($bread))}}">
					<?php
					echo trans('common.' . $bread);
					?>
				</a>
			</li>

			<!--							<li>
											<a href="#">Roles</a>
										</li>-->

			<li class="active">
				{{trans(strtolower($bread) . '.' . session('active_breadcrumb'))}}
			</li>
		</ul><!-- .breadcrumb -->

<!--		<div class="nav-search" id="nav-search">
			<form class="form-search">
				<span class="input-icon">
					<input type="text" placeholder="{{trans('common.Search')}} ..." class="nav-search-input" id="nav-search-input" autocomplete="off" />
					<i class="icon-search nav-search-icon"></i>
				</span>
			</form>
		</div> #nav-search -->
	</div>

	<?php
}
?>