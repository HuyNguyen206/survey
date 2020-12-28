<b>Notification from system survey "customer voice" -
@if($param['results']['bad'.$param['sale_net_type']]['other']['alertGood'])
	<span style="color: #0070C0;">Customer was satisfied with {{$param['results']['bad'.$param['sale_net_type']]['other']['mainTitle']}}</span>
@else
	<span style="color: #FF0000;">Customer was unsatisfied with {{$param['results']['bad'.$param['sale_net_type']]['other']['mainTitle']}}</span>
@endif
</b>
</br>
</br>
<b>Point of Contact:</b> <span style="color: #0070C0;">{{$param['poc']}}</span>
<b>Record channel:</b> <span style="color: #0070C0;">{{$param['channel']}}</span>
<b>Record Time:</b> <span style="color: #0070C0;">{{$param['time']}}</span>
</br>
<b>Survey code:</b>  <span style="color: #0070C0;">{{$param['code']}}</span>
<b>End time of {{$param['type']}}:</b>  <span style="color: #0070C0;">{{$param['timeComplete']}}</span>
</br>
</br>
<b>Customer name:</b>  <span style="color: #0070C0;">{{$param['name']}}</span>
<b>ID No:</b>  <span style="color: #0070C0;">{{$param['shd']}}</span>
<b>Phone:</b>  <span style="color: #0070C0;">{{$param['phone']}}</span>
</br>
<b>Address:</b>  <span style="color: #0070C0;">{{$param['address']}}</span>
</br>

</br>
<b>Survey Information:</b></br>
<table border="1" style="border-collapse: collapse;">
	<tr>
		<td>Object</td>
		<td>Customer evaluation</td>
		<td>Record by {{$param['results']['bad'.$param['sale_net_type']]['other']['recordBy']}}</td>
	</tr>
	@if(!empty($param['results']['bad'.$param['sale_net_type']]['main']))
		<?php
		$result = $param['results']['bad'.$param['sale_net_type']]['main'];
		$temp = explode(':', $result['object']);
		?>
		<tr>
			@if(count($temp) == 2)
				<td><b>{{$temp[0]}}: <span style="color: #0070C0;">{{$temp[1]}}</span></b></td>
			@else
				<td>{{$temp[0]}}</td>
			@endif
			<td><b><span style="color: #0070C0;">{{$result['csat']}} (CSAT = {{$result['point']}})</span>  <span><img src="http://cem.opennet.com.kh/assets/img/Point_0{{$result['point']}}.png" width="20" height="20"></span></b></td>
			<td>
				<b>
					@if(!empty($result['typeError']))
						Error type: <span style="color: #0070C0;">{{$result['typeError']}}</span></br>
						Note: <span style="color: #0070C0;">{{$result['note']}}</span>
					@else
						<span style="color: #0070C0;">{{$result['note']}}</span>
					@endif
				</b>
			</td>
		</tr>
	@endif
	@foreach($param['results']['bad'.$param['sale_net_type']] as $keyRes => $result)
		@if($keyRes !== "main" && $keyRes !== "other")
			<?php $temp = explode(':', $result['object']);?>
			<tr>
				@if(count($temp) == 2)
					<td>{{$temp[0]}}: <span style="color: #0070C0;">{{$temp[1]}}</span></td>
				@else
					<td>{{$temp[0]}}</td>
				@endif
				<td><span style="color: #0070C0;">{{$result['csat']}} (CSAT = {{$result['point']}})</span>  <span><img src="http://cem.opennet.com.kh/assets/img/Point_0{{$result['point']}}.png" width="20" height="20"></span></td>
				<td>
					@if(!empty($result['typeError']))
                        Error type: <span style="color: #0070C0;">{{$result['typeError']}}</span></br>
                        Note: <span style="color: #0070C0;">{{$result['note']}}</span>
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
	Manager can access link customer Voice(<a href="http://cem.opennet.com.kh">http://cem.opennet.com.kh</a>) to see for more detail</br>
@elseif($param['num_type'] == 12)
	Manager receiving email must contact the customer according to the customer care script to find out the case that reflects the customer's details and proceed with the handling as Regulation!</br>
@else
    Manager received email must go to tool Customer Voice(<a href="http://cem.opennet.com.kh">http://cem.opennet.com.kh</a>) to listen the record, learn about the case problem and start to solve the problem follow the policy!</br>
@endif
<a href="{{$param['confirm_link']}}">Confirm</a>