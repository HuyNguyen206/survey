<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Surveys</title>

		<meta name="description" content="User login page" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!-- basic styles -->
		<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" />
		<link rel="stylesheet" href="{{asset('assets/css/font-awesome.min.css')}}" />

		<!-- ace styles -->

		<link rel="stylesheet" href="{{asset('assets/css/ace.min.css')}}" />
		<link rel="stylesheet" href="{{asset('assets/css/ace-rtl.min.css')}}" />
	</head>

	<script type="text/javascript">
		window.jQuery || document.write("<script src='{{asset('assets/js/jquery-2.0.3.min.js')}}'>"+"<"+"/script>");
	</script>

	<!-- <![endif]-->

	<script type="text/javascript">
		if("ontouchend" in document) document.write("<script src='{{asset('assets/js/jquery.mobile.custom.min.js')}}'>"+"<"+"/script>");
	</script>

	<body class="login-layout">
		<div class="main-container">
			@yield('content')
		</div><!-- /.main-container -->

		<script type="text/javascript">
			function show_box(id) {
			 jQuery('.widget-box.visible').removeClass('visible');
			 jQuery('#'+id).addClass('visible');
			}
		</script>
	</body>
</html>
