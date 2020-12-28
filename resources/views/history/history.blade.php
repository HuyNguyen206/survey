@foreach ($objs as $obj)
<tr>
  	<td rowspan="2" width="5px" align="center" valign="middle" style="border-bottom:1px #FF0D0D solid;"></td> <!--class='td1'-->
    <td class='td2' align="top" valign="top" bgcolor="{bgcolor}">{!! $obj->SupportTypeName !!} - <b>{!! $obj->HelpdeskIPPhone !!}</b> <br/> {ext}: <font color="#8C0000"><b>{!! $obj->ContactPhone !!}</b></font></td>
    <td class='td2' width="135px" align="top" valign="top" bgcolor="{bgcolor}">{!! $obj->time_start !!}: <font color="#8C0000"><b>{!! $obj->StartDate !!}</b></font></td>
    <td class='td2' width="135px" align="top" valign="top" bgcolor="{bgcolor}">{!! $obj->time_end !!}: <font color="#8C0000"><b>{!! $obj->history_end !!}</b></font></td>
    <td class='td2' width="235px" align="top" valign="top" bgcolor="{bgcolor}">{!! $obj->HelpdeskName !!}: <b>{!! $obj->DivisionName !!}</b><br /><font color="RED">{!! $obj->history_customer_phone !!}</font></td>
    <td class='td2' width='115px' align="top" valign="top" bgcolor="{bgcolor}"><b style="color:#FF0000">{!! $obj->CallerTypeName !!}</b></td>
</tr>
<tr>
</tr>
@endforeach