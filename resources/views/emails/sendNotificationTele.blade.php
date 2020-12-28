<b>Thông báo từ hệ thống KS-CSKH sau </b><span style="color: red;"><b>{{$param['type']}}</b></span></br>
<b>Ca chăm sóc: </b><span style="color: red;">{{$param['time']}} – Mã KS: {{$param['code']}}</span></br>
<b>Nhân viên được đánh giá: </b><span style="color: red;">{{$param['team']}}</span></br>
<table border="1" style="border-collapse: collapse;">
	<tr>
		<td rowspan="4"><b>Khách hàng</b></td>
		<td>{{$param['name']}}</td>
	</tr>
	<tr>
		<td><b>Số hợp đồng: </b>{{$param['shd']}}</td>
	</tr>
	<tr>
	<td><b>Địa chỉ: </b>{{$param['address']}}</td>
	</tr>
	<tr>
		<td><b>SĐT: </b>{{$param['phone']}}</td>
	</tr>
</table>
<b>Đánh giá của KH: </b><span style="color: red;">{{$param['csat']}} (CSAT = {{$param['point']}})</span>  <span><img src="http://survey.fpt.vn/assets/img/Point_0{{$param['point']}}.png" width="20" height="20"></span></br>
<b>Ghi chú của NVCS: </b><span style="color: red;">{{$param['note']}}</span></br>
<a href="{{$param['confirm_link']}}">Xác nhận</a>;