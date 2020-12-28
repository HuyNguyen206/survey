<b>Thông báo từ Hệ thống KS-CSKH “Customer Voice”
@if($param['results']['bad'.$param['sale_net_type']]['other']['alertGood'])
	- <span style="color: #0070C0;">Khách hàng rất hài lòng với {{$param['results']['bad'.$param['sale_net_type']]['other']['mainTitle']}}</span></b></br>
@else
	- <span style="color: #FF0000;">Khách hàng không hài lòng với {{$param['results']['bad'.$param['sale_net_type']]['other']['mainTitle']}}</span></b></br>
@endif
</br>
<b>Điểm tiếp xúc:</b> <span style="color: #0070C0;">{{$param['poc']}}</span>
<b>Kênh ghi nhận:</b> <span style="color: #0070C0;">{{$param['channel']}}</span>
<b>Thời gian ghi nhận:</b> <span style="color: #0070C0;">{{$param['time']}}</span>
</br>
<b>Mã KS-CSKH:</b>  <span style="color: #0070C0;">{{$param['code']}}</span>
<b>Thời gian hoàn tất {{$param['type']}}:</b>  <span style="color: #0070C0;">{{$param['timeComplete']}}</span>
</br>
</br>
<b>Tên chủ hợp đồng:</b>  <span style="color: #0070C0;">{{$param['name']}}</span>
<b>Số hợp đồng:</b>  <span style="color: #0070C0;">{{$param['shd']}}</span>
<b>SĐT:</b>  <span style="color: #0070C0;">{{$param['phone']}}</span>
</br>
<b>Địa chỉ:</b>  <span style="color: #0070C0;">{{$param['address']}}</span>
</br>

</br>
<b>Phản ánh, đánh giá của Khách hàng:</b></br>
<table border="1" style="border-collapse: collapse;">
	<tr>
		<td>Đối tượng</td>
		<td>Đánh giá của KH</td>
		<td>Ghi nhận từ {{$param['results']['bad'.$param['sale_net_type']]['other']['recordBy']}}</td>
	</tr>
	@foreach($param['results']['bad'.$param['sale_net_type']] as $keyRes => $result)
		@if($keyRes !== "other")
		<?php $temp = explode(':', $result['object']);?>
			<tr>
				@if(count($temp) == 2)
					<td>{{$temp[0]}}: <span style="color: #0070C0;">{{$temp[1]}}</span></td>
				@else
					<td>{{$temp[0]}}</td>
				@endif
				<td><span style="color: #0070C0;">{{$result['csat']}} (CSAT = {{$result['point']}})</span>  <span><img src="http://survey.fpt.vn/assets/img/Point_0{{$result['point']}}.png" width="20" height="20"></span></td>
				<td>
					@if(!empty($result['typeError']))
						Phân loại lỗi: <span style="color: #0070C0;">{{$result['typeError']}}</span></br>
						Ghi chú khác: <span style="color: #0070C0;">{{$result['note']}}</span>
					@else
						<span style="color: #0070C0;">{{$result['note']}}</span>
					@endif
				</td>
			</tr>
		@endif
	@endforeach
</table>
</br>
@if($param['results']['bad'.$param['sale_net_type']]['other']['alertGood'])
	Quản lý có thể truy cập hệ thống Customer Voice(<a href="https://cem.fpt.vn">https://cem.fpt.vn</a>) để xem thêm chi tiết</br>
@else
	Quản lý nhận được email phải vào Tool Customer Voice(<a href="https://cem.fpt.vn">https://cem.fpt.vn</a>) tìm hiểu trường hợp phản ánh chi tiết của KH và tiến hành xử lý như Quy định!</br>
@endif