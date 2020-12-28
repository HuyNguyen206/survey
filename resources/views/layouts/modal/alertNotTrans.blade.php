<!----------------------------alert-------------------------------->
<?php
if (session('alert') !== null){
	?>
		<div class="alert alert-warning">
			<strong>
				<i class="icon-warning-sign"></i>
			Cảnh báo!
			</strong>
			{{session('alert')}}
			<br />
		</div>
	<?php
}

if (session('success') !== null){
	?>
		<div class="alert alert-success">
			<strong>
				<i class="icon-warning-sign"></i>
			Thành công!
			</strong>
			{{session('success')}}
			<br />
		</div>
	<?php
}

if (session('fail') !== null){
	?>
		<div class="alert alert-danger">
			<strong>
				<i class="icon-remove"></i>
				Thất bại!
			</strong>
			{{session('fail')}}
			<br />
		</div>
	<?php
}
?>
<!----------------------------end alert-------------------------------->