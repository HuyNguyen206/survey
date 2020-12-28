/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).bind("ajaxSend", function () {
    $('#loading-image').css('display', 'inline-block');
}).bind("ajaxComplete", function () {
    $('#loading-image').css('display', 'none');
});
formChecklist = '#formChecklistAction2 ';
lang=$('input[name=lang]').val();
/* Xử lý checklist
 */
//Lấy danh sách hiển thị thông tin checklist
$('#createCL').click(function () {
    $('.modal-body-checklist').html('');
    $('#formChecklist').modal('show');
    $.ajax({
        url: API_URL + "checklist/getCheckList",
        cache: false,
        type: "POST",
        dataType: "JSON",
        data: {'ObjID': $('input[name=objID]').val(), '_token': $('input[name=_token]').val()},
        success: function (response) {
            var tableContent = '', colorTable
//         console.log(response)
            //Co dữ liệu Cl
            if (response.hasOwnProperty('data'))
            {
                $.each(response.data, function (index, value) {
                    if (value.onCom == false)
                    {
//                        finnish=false;
                        colorTable = 'white';
                    } else
                        colorTable = 'springgreen';
//                var colorTable = value.onCom == true ? 'springgreen' : 'white';
                    tableContent += (lang == 'vi') ?  '<table border="1" cellspacing="2" style="text-align: center;    font-size: 14px; background-color:' + colorTable + '"><tr><td>SMS</td><td>' + value.MobilePhone + '</td><td>Ngày gọi Complain</td><td >' + value.LastCall + '</td><td>Tổ con của đối tác phân công lần 2</td><td>' + value.SubSupporter2 + '</td><td>Tên loại khách hàng</td><td >' +
                            value.CusFormName + '</td><td>Cáp Indoor</td><td>' + value.IDCLength + '</td></tr>'
                            + '<tr><td>Mã checklist</td><td>' + value.ID + '</td><td>Phân công đối tác xử lý lần 1+</td><td>' + value.Supporter + '</td><td>Tổ con của đối tác phân công lần 3</td><td >' + value.SubSupporter3 + '</td><td>Loại dịch vụ+</td><td >' + value.LocalType + '</td><td>Cáp outdoor</td><td >' + value.ODCLength + '</td></tr>'
                            + '<tr><td>Mã khách hàng</td><td >' + value.ObjID + '</td><td>Đối tác xử lý lần 2</td><td>' + value.Supporter1 + '</td><td>Ngày đóng checklist</td><td >' + value.CusCloseDate + '</td><td>Tên loại dịch vụ</td><td>' + value.LocalTypeName + '</td><td>VIP</td><td>' + value.Vip + '</td></tr>'
                            + '<tr><td>Người nhập</td><td>' + value.CreateBy + '</td><td>Đối tác xử lý lần 3</td><td >' + value.Supporter2 + '</td><td>Ý kiến khách hàng</td><td>' + value.CusDesc + '</td><td>Điện thoại</td><td >' + value.Location_Phone + '</td><td>HOT</td><td>' + value.HOT + '</td></tr>'
                            + '<tr><td>Ngày tạo checklist</td><td>' + value.ADate + '</td><td>Đối tác xử lý lần 4</td><td>' + value.Supporter3 + '</td><td>Lý do FPT đóng</td><td>' + value.CloseCLDesc + '</td><td>Số hợp đồng</td><td>' + value.Contract + '</td><td>Tên của Final_Status</td><td>' + value.Final_Status_Desc + '</td></tr>'
                            + '<tr><td>Tình trạng</td><td >' + value.Status + '</td><td>Ghi chú ban đầu</td><td >' + value.Init_Desc + '</td><td>Tình trạng khách hàng</td><td> ' + value.CusStatus + '</td><td>Tên khách hàng</td><td>' + value.FullName + '</td><td>Ghi chú</td><td>' + value.Final_Desc + '</td></tr>'
                            + '<tr><td>Tình trạng sự cố ban đầu</td><td>' + value.Init_Status + '</td><td>Tình trạng</td><td>' + value.Now_Status1 + '</td><td>Khách hàng đóng checklist</td><td>' + value.CloseCLMethod + '</td><td>Tập điểm</td><td>' + value.ODCCableType + '</td></tr>'
                            + '<tr><td>Tên sự cố ban đầu</td><td>' + value.Init_Status_Name + '</td><td>Tình trạng</td><td>' + value.Now_Status2 + '</td><td>Checklist khẩn</td><td> ' + value.Urgent + '</td><td>Địa chỉ lắp đặt</td><td>' + value.Address + '</td></tr>'
                            + '<tr><td>Tình trạng</td><td>' + value.Final_Status + '</td><td>Tình trạng</td><td>' + value.Now_Status3 + '</td><td>Ngày tạo checklist khẩn</td><td>' + value.CLDate + '</td><td>TS thi công</td><td>' + value.AreaID + '</td></tr>'
                            + '<tr><td>Ghi chú</td><td>' + value.Description + '</td><td>Kết quả xử lý lần 1</td><td> ' + value.Result1 + '</td><td>Người đóng checklist</td><td> ' + value.StaffName + '</td><td>Vùng miền</td><td>' + value.LocationID + '</td></tr>'
                            + '<tr><td>Ngày phân công nhân viên</td><td> ' + value.AssignDate + '</td><td>Kết quả xử lý lần 2</td><td>' + value.Result2 + '</td><td>Tên truy cập+</td><td>' + value.Name + '</td><td>Quận bảo trì</td><td>' + value.Support_District + '</td></tr>'
                            + '<tr><td>Ngày đóng checklist</td><td> ' + value.FinishDate + '</td><td>Kết quả xử lý lần 3</td><td>' + value.Result3 + '</td><td>Địa chỉ lắp đặt</td><td>' + value.Location + '</td><td>Quận trong địa chỉ lắp đặt của khách</td><td>' + value.Location_District + '</td></tr>'
                            + '<tr><td>Vị trí xảy ra kết quả xử lý</td><td> ' + value.SupportStatus + '</td><td>Kết quả xử lý lần 4</td><td>' + value.Result4 + '</td><td>Chi nhánh</td><td>' + value.BranchCode + '</td><td>Thông tin khuyến mãi</td><td>' + value.Promotion + '</td></tr>'
                            + '<tr><td>Độ ưu tiên</td><td> ' + value.Priority + '</td><td>Ngày hẹn bảo trì</td><td>' + value.AppointmentDate + '</td><td>Người liên hệ+</td><td> ' + value.Location_Name + '</td><td>Truy cập lần đầu</td><td> ' + value.FirstAccess + '</td></tr>'
                            + '<tr><td>Số lần complain</td><td>' + value.TotalCall + '</td><td>Tổ con của đối tác phân công lần 1</td><td >' + value.SubSupporter1 + '</td><td>Loại khách hàng</td><td>' + value.CusForm + '</td><td>Thông tin hỗ trợ onsite</td><td>' + value.SupportDay + '</td></tr></table> <br/>'
                    : 
                            '<table border="1" cellspacing="2" style="text-align: center;    font-size: 14px; background-color:' + colorTable + '"><tr><td>SMS</td><td>' + value.MobilePhone + '</td><td>Date Call Complain</td><td >' + value.LastCall + '</td><td>Sub Supporter second assign</td><td>' + value.SubSupporter2 + '</td><td>Type Name Customer</td><td >' +
                            value.CusFormName + '</td><td>Cable Indoor</td><td>' + value.IDCLength + '</td></tr>'
                            + '<tr><td>Checklist code</td><td>' + value.ID + '</td><td>Sub Supporter first assign+</td><td>' + value.Supporter + '</td><td>Sub Supporter third assign</td><td >' + value.SubSupporter3 + '</td><td>Type service</td><td >' + value.LocalType + '</td><td>Cable outdoor</td><td >' + value.ODCLength + '</td></tr>'
                            + '<tr><td>Customer code</td><td >' + value.ObjID + '</td><td>Partner Process second time</td><td>' + value.Supporter1 + '</td><td>Date Close checklist</td><td >' + value.CusCloseDate + '</td><td>Name type service</td><td>' + value.LocalTypeName + '</td><td>VIP</td><td>' + value.Vip + '</td></tr>'
                            + '<tr><td>Created By</td><td>' + value.CreateBy + '</td><td>Partner Process third time</td><td >' + value.Supporter2 + '</td><td>Customer opinion</td><td>' + value.CusDesc + '</td><td>Phone</td><td >' + value.Location_Phone + '</td><td>HOT</td><td>' + value.HOT + '</td></tr>'
                            + '<tr><td>Create Date checklist</td><td>' + value.ADate + '</td><td>Partner Process fourth time</td><td>' + value.Supporter3 + '</td><td>Reason FPT close</td><td>' + value.CloseCLDesc + '</td><td>ContractNumber</td><td>' + value.Contract + '</td><td>Name of Final_Status</td><td>' + value.Final_Status_Desc + '</td></tr>'
                            + '<tr><td>Status</td><td >' + value.Status + '</td><td>First Note</td><td >' + value.Init_Desc + '</td><td>Customer status</td><td> ' + value.CusStatus + '</td><td>Name customer</td><td>' + value.FullName + '</td><td>Note</td><td>' + value.Final_Desc + '</td></tr>'
                            + '<tr><td>Status first incident</td><td>' + value.Init_Status + '</td><td>Status</td><td>' + value.Now_Status1 + '</td><td>Customer close checklist</td><td>' + value.CloseCLMethod + '</td><td>ODCCable Type</td><td>' + value.ODCCableType + '</td></tr>'
                            + '<tr><td>Name first incident</td><td>' + value.Init_Status_Name + '</td><td>Status</td><td>' + value.Now_Status2 + '</td><td>Urgent checklist</td><td> ' + value.Urgent + '</td><td>Setup Address</td><td>' + value.Address + '</td></tr>'
                            + '<tr><td>Status</td><td>' + value.Final_Status + '</td><td>Staus</td><td>' + value.Now_Status3 + '</td><td>Date Create urgent checklist</td><td>' + value.CLDate + '</td><td>Deployment Area</td><td>' + value.AreaID + '</td></tr>'
                            + '<tr><td>Note</td><td>' + value.Description + '</td><td>Result Process first time</td><td> ' + value.Result1 + '</td><td>Person close checklist</td><td> ' + value.StaffName + '</td><td>Location</td><td>' + value.LocationID + '</td></tr>'
                            + '<tr><td>Date assign staff</td><td> ' + value.AssignDate + '</td><td>Result Process second time</td><td>' + value.Result2 + '</td><td>Name access</td><td>' + value.Name + '</td><td>Support District</td><td>' + value.Support_District + '</td></tr>'
                            + '<tr><td>Date close checklist</td><td> ' + value.FinishDate + '</td><td>Result process third time</td><td>' + value.Result3 + '</td><td>Setup address</td><td>' + value.Location + '</td><td>Setup district </td><td>' + value.Location_District + '</td></tr>'
                            + '<tr><td>Position occured result process</td><td> ' + value.SupportStatus + '</td><td>Result process fourth time</td><td>' + value.Result4 + '</td><td>Branch</td><td>' + value.BranchCode + '</td><td>Promotion Informantion</td><td>' + value.Promotion + '</td></tr>'
                            + '<tr><td>Priority</td><td> ' + value.Priority + '</td><td>Appointment Date maintain</td><td>' + value.AppointmentDate + '</td><td>Contact Name</td><td> ' + value.Location_Name + '</td><td>First Access</td><td> ' + value.FirstAccess + '</td></tr>'
                            + '<tr><td>Quantity complain</td><td>' + value.TotalCall + '</td><td>Sub Support first assign</td><td >' + value.SubSupporter1 + '</td><td>Type customer</td><td>' + value.CusForm + '</td><td>Support Information onsite</td><td>' + value.SupportDay + '</td></tr></table> <br/>' ;
                });
            } else
            {
                tableContent = (lang == 'vi') ? 'Không có dữ liệu' : 'No data'
            }
            $('.modal-body-checklist').html(tableContent);
        },
        error: function (response) {
            if (response.code == 800)
            {
                alert(response.msg)
            } else {
                alert((lang == 'vi') ?'Lỗi hệ thống' : 'Error System');
            }
        }
    });
})

//Kiểm tra còn checklist tồn hay không
$('#createCLButton').click(function () {
    $.ajax({
        url: API_URL + "checklist/SupportListCheck",
        cache: false,
        type: "POST",
        dataType: "JSON",
        data: {'ObjID': $('input[name=objID]').val(), '_token': $('input[name=_token]').val()},
        success: function (response) {
            if (response.hasClStore == true)
                alert((lang == 'vi') ?'Hợp đồng này còn checklist tồn' : 'This contract had stored checklist')
            else
            {
                $('#formChecklist').modal('hide');
                $('#incidentflag').css('display', 'none')
                $('#desflag').css('display', 'none')
                $('#modemflag').css('display', 'none')
                $('#assignflag').css('display', 'none')
//                $('.OwnerTypeFlag').css('display', 'none')
                $('#createCLButtonNow').css('display', 'inline-block')
                $('#formChecklistAction2')[0].reset();
                $('#formChecklistAction').modal('show');
                 $('#skillSchedule').html('');
                 var listError=(lang == 'vi') ?
                     '<option disabled selected>Chọn</option><option value="1">+ Có tín hiệu nhưng không truy cập được</option><option value="8">+ Đèn Sang nhưng Mạng rất chậm</option><option value="9">+ Đèn Sáng nhưng không có tín hiệu</option>'
                 +'<option value="6">+ Đèn sáng nhưng không ổn định</option><option value="2">+ rớt mạng thường xuyên</option><option value="3">+ Mạng chậm không đúng cam kết</option><option value="4">+ Không tín hiệu </option>' 
                 +'<option value="10">+  Download upload không ổn định</option><option value="12">+ Trời mưa, rớt mạng</option><option value="17">+  Lỗi thiết bị</option>'
                 +'<option value="18">+ Lỗi Ivoice(Nghe/ Nói/ Âm)</option><option value="20">+ Net suy yếu </option><option value="21">+ Game lag </option>'
                 +'<option value="22">+ Khổi phục dịch vụ </option><option value="23">+ Cable đứt </option><option value="24">+ Mất tín hiệu fiber </option>'
                 +'<option value="25">+ Rớt thường xuyên </option><option value="26">+  Mạng luôn mất kết nối</option><option value="27">+ Không thể vào mạng(Không thể kết nối wifi)</option>'
                 +'<option value="28">+  Thay đổi password wifi</option><option value="29">+ Mất tên Wifi</option><option value="30">+  Tín hiệu Wifi Modem/router không ổn định</option>'
                 +'<option value="31">+ Vấn đề kết nối Lan</option><option value="32">+ Mạng không thể sử dụng với camera bảo mật </option><option value="33">+ Thay đổi vị ví modem</option>'        
                 +'<option value="35">+ DSP không đúng</option><option value="36">+ Modem/router mất cấu hình</option><option value="37">+ Lỗi modem router cổng LAN/WAN</option>'
                 +'<option value="38">+ lưu lượng down/up không đủ</option><option value="39">+ Router nhận dạng nhưng không truy cập được mạng</option><option value="40">+ Tín hiệu ADSL/TTH Tốt nhưng mạng luôn không ổn định</option>'
                 +'<option value="41">+ Không thể truy cập website</option><option value="42">+ Không thể gửi nhận email,</option><option value="43">+ Không thể kết nối tới server VPN</option>'
                 +'<option value="44">+ Truy cập website chậm</option><option value="47">+  Khách hàng cần giúp cấu hình Microtik</option>'
                     +'<option value="48">+ Truy cập facebook chậm</option><option value="49">+ Bảo trì định kỳ</option><option value="34">+ Có IPW - Không kết nối</option><option value="0">+ Tình trạng khác...</option>'
                    : 
                 '<option disabled selected>Select</option><option value="1">+ Have IPWan - not access</option><option value="8">+ Lights ADSL good but netwwork very slow</option><option value="9">+ Lights ADSL good but not get IPWAN </option>'
                 +'<option value="6">+ Lights ADSL flash, unstable</option><option value="2">+ Regular dropout network</option><option value="3">+ Slow Network wrong commitment</option><option value="4">+ Not Sign ADSL </option>' 
                 +'<option value="10">+ Down stream/ UP stream unstable</option><option value="12">+ It is raining, dropout network</option><option value="17">+ Equipment error</option>'
                 +'<option value="18">+ Ivoice error ( Hear / speak / Tone)</option><option value="20">+ Net flickered </option><option value="21">+ Game lag </option>'
                 +'<option value="22">+ Restore Service </option><option value="23">+ Break cable </option><option value="24">+ Lose fiber signal </option>'
                 +'<option value="25">+ DropPing </option><option value="26">+ Internet  connection always disconnect</option><option value="27">+ Can’t join network (Can’t connect WiFi)</option>'
                 +'<option value="28">+ Change Password WiFi</option><option value="29">+ Lost WiFi name</option><option value="30">+ Signal  WiFi modem/router unstable</option>'
                 +'<option value="31">+ LAN connection problem</option><option value="32">+ Internet can’t use with camera security </option><option value="33">+ Change location modem</option>'        
                 +'<option value="35">+ Invalid DSP</option><option value="36">+ Modem/router lost configuration</option><option value="37">+ Modem router error port LAN/WAN</option>'
                 +'<option value="38">+ Downstream/upstream not enough</option><option value="39">+ Router Access point not internet access</option><option value="40">+ Signal ADSL/FTTH good but Internet access always unstable</option>'
                 +'<option value="41">+ Can’t access website</option><option value="42">+ Can’t receive/send e-mail outlook</option><option value="43">+ Can’t connect to VPN Server</option>'
                 +'<option value="44">+ Access website slow</option><option value="47">+ Customer need to help configure Microtik</option>'
                 +'<option value="48">+ Unifi -Customer slow facebook</option><option value="49">+ Maintenance periodic</option><option value="34">+ Having IPW - Not Connected</option><option value="0">+ Other status...</option>';
                 $('#clInitStatus').html(listError)
                //Lấy grouppoint
                $.ajax({
                    url: API_URL + "checklist/getGroupPoint",
                    cache: false,
                    type: "POST",
                    dataType: "JSON",
                    data: {'ContractNum': $('input[name=contractNum]').val(), '_token': $('input[name=_token]').val()},
                    success: function (response) {
                        console.log(response)
                        if (response.code == 200)
                        {
                            if(response.groupPoint != '' && response.pop != '')
                            {
                            $('#formChecklistAction2 input[name=Grouppoints]').val(response.groupPoint);
                            $('#formChecklistAction2 input[name=POP]').val(response.pop);
                        }
                        else
                        {
                            alert((lang == 'vi' ? 'Check lại thông tin tập điểm của hợp đồng' : 'Check Grouppoints-Pop information contract'));
                             $('#formChecklistAction').modal('hide');
                        }
                        } else
                        {
                            alert(response.msg);
                        }
                    },
                    error: function (response) {
                        alert(response.msg);
                    }
                });
                //Hiển thị datepicker
                $(".AppointmentDate").datepicker({
                    changeYear: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd',
                    minDate: 0,
                }).datepicker("setDate", new Date());
            }
        },
        error: function (response) {
            alert(response.data);
        }
    });
});

//Lấy thông tin tổ đội checklist
$('#clInitStatus').change(function () {
    $.ajax({
        url: API_URL + "checklist/getNameUser",
        cache: false,
        type: "POST",
        dataType: "JSON",
//                data: {'_token': $('input[name=_token]').val()},
        data: {'ObjID': $('input[name=objID]').val(), 'Grouppoints': $('#formChecklistAction2 input[name=Grouppoints]').val(), 'POP': $('#formChecklistAction2 input[name=POP]').val(),
            'Type': $('input[name=typeSurvey]').val(), 'InitStatus': $(this).val(), '_token': $('input[name=_token]').val()},
        success: function (response) {
            var responseSubID = response.responseSubID;
            if (responseSubID.length == 0)
            {
                alert((lang == 'vi') ?'Thiếu thông tin tổ đội trả về' : 'Missing SubSupporter Information Return')
                $(formChecklist + 'input[name=Supporter]').val('');
                $(formChecklist + 'input[name=SubSupporter]').val('');
                $('#skillSchedule').html('');
            } else
            {
                if (responseSubID[0].ResultDepID == '' || responseSubID[0].ResultSubID == '')
                {
                     alert((lang == 'vi') ?'Thiếu thông tin tổ đội trả về' : 'Missing SubSupporter Information Return')
                    $('#skillSchedule').html('');
                } else
                {
                    $(formChecklist + 'input[name=Supporter]').val(responseSubID[0].ResultDepID);
                    //Nhớ trả lại code sau khi test
                    $(formChecklist + 'input[name=SubSupporter]').val(responseSubID[0].ResultSubID);
                    //Dữ liệu test
                    // $(formChecklist + 'input[name=SubSupporter]').val(2);
                    $(formChecklist + 'input[name=DeptID]').val(responseSubID[0].ResultCode);
                }
            }
            $(formChecklist + 'input[name=CreateBy]').val(response.name);
            $(formChecklist + 'input[name=ObjId]').val($('input[name=objID]').val());
            $(formChecklist + 'input[name=Type]').val(2);
            $(formChecklist + 'input[name=RequestFrom]').val(23);
        },
        error: function (response) {
            if (response.code == 800)
            {
                alert(response.msg)
            } else
            {
                alert((lang == 'vi') ?'Lỗi hệ thống' : 'Error System');
            }
        }
    });
});

//Reset lại các mốc thời gian khi đổi ngày hẹn
$('input[name=AppointmentDate]').change(
        function () {
            $('#getDateInfo').prop('checked', false);
            $('#skillSchedule').html('');
        });

//Lấy thông tin múi giờ tick hẹn checklist
function getDateInfo() {
//            if ($scope.client == true) {
    var supporter = $(formChecklist + 'input[name=Supporter]').val(), subSupporter = $(formChecklist + 'input[name=SubSupporter]').val()
    if (invalidValue.indexOf(supporter) !== -1 || invalidValue.indexOf(subSupporter) !== -1)
    {
        alert((lang == 'vi') ? 'Thiếu thông tin tổ đội phân công nên không tạo được Checklist' : 'Can not create Checklist due to missing SubSupporter Information')
    } else if ($(formChecklist + 'input[name=AppointmentDate]').val() == null || $(formChecklist + 'input[name=AppointmentDate]').val() == '')
    {
        alert((lang == 'vi') ? 'Vui lòng chọn ngày phân công' : 'Please type assignment date')
    } else
    {
        var currentDate = getCurrentDate();;
       var timeInfo = {Supporter: supporter, SubID: subSupporter, AppointmentDate: $(formChecklist+'input[name=AppointmentDate]').val(),
           Date: currentDate, LocationID:$('input[name=LocationID]').val()};
//  console.log(timeInfo);
        var url = API_URL + "checklist/getDateInfo";
        $.ajax({
            url: url,
            cache: false,
            type: "POST",
            dataType: "JSON",
            data: {'datapost': timeInfo, '_token': $('input[name=_token]').val()},
            success: function (response) {
                console.log(response);
                if (response.code === 200) {
                    var tableContent = '';
                    $.each(response.reponseDateInfo, function (index, value) {
                        var available = (value.TimezoneCode == -1 || ($('select[name=Init_Status]').val() == 4 && (value.TimezoneCode18 == 19 || value.TimezoneCode18 == 21))) ? 'disabled' : '';
                        tableContent += '<tr><td> <input style="width: 17px;height:17px;" type="checkbox" ng-model="AD.Timezone" ' + available + ' name="Timezone" value="' + value.Timezone + '">' + value.Timezone + '</td> <td>' + value.TimeCount + '</td> <td>' + value.TimezoneAbility + '</td></tr>';
                    });
                     tableContent = tableContent != '' ? tableContent  : '<tr><td colspan="3">No data</td></tr>'
                    $('#skillSchedule').html(tableContent);
                } else {
                    alert((lang == 'vi') ?'Có lỗi xảy ra' : 'Error happen')
                }
            },
            error: function (response) {
                if (response.code == 800)
                {
                    alert(response.msg);
                } else {
                    alert((lang == 'vi') ?'Lỗi hệ thống' : 'Error System');
                }
                console.log(response);
            }
        });
    }
}

//Kiểm tra chọn ngày hẹn
$('#getDateInfo').change(
        function () {
            if ($(this).is(':checked')) {
//            alert('checked');
                getDateInfo()
            } else
            {
                $('#skillSchedule').html('');
            }
        });

//Gửi thông tin checklist
$('#createCLButtonNow').click(function () {
    var msg = '';
    if ($(formChecklist + 'select[name=Init_Status]').val() == null)
    {
        msg += '\n Vui lòng chọn loại sự cố';
        $(formChecklist + '#incidentflag').css('display', 'inline-block')
    } else
    {
        $(formChecklist + '#incidentflag').css('display', 'none')
    }
    if ($(formChecklist + 'textarea[name=Description]').val() == null || $(formChecklist + 'textarea[name=Description]').val() == '')
    {
        msg += ' \n Vui lòng ghi chú';
        $(formChecklist + '#desflag').css('display', 'inline-block')
    } else
    {
        $(formChecklist + '#desflag').css('display', 'none')
    }
    if($('#getDateInfo').is(':checked'))
    {
        if($('#skillSchedule').text() !== 'No data')
        {
            if (!$(formChecklist + 'input[name=Timezone]').is(':checked')) {
                msg += ' \n Vui lòng chọn múi thời gian';
                $(formChecklist + '#assignflag').css('display', 'inline-block')
            } else {
                $(formChecklist + '#assignflag').css('display', 'none')
            }
        }
        else
        {
            $(formChecklist + '#assignflag').css('display', 'none')
        }
    }
    else
    {
        msg += ' \n Vui lòng chọn múi thời gian';
        $(formChecklist + '#assignflag').css('display', 'inline-block')
    }

//    }
    if (msg == '')
    {

        $('#createCLButtonNow').css('display', 'none')
        var url = API_URL + "checklist/createCL";
        var dataFormList = $('#formChecklistAction2').serializeArray();
        var dataForm = {};
        for (var i = 0; i < dataFormList.length; i++) {
            dataForm[dataFormList[i]['name']] = dataFormList[i]['value'];
        }
        dataForm['typeSurvey'] = $('input[name=typeSurvey]').val();
        dataForm['contractNum'] = $('input[name=contractNum]').val();
        dataForm['codedm'] = $('input[name=codedm]').val();
        var datadate = {};
        datadate['AppointmentDate'] = dataForm['AppointmentDate']
        datadate['isChange'] = dataForm['isChange']
        datadate['Department'] = dataForm['Department']
        datadate['Timezone'] = dataForm['Timezone']
        datadate['LogonUser'] = dataForm['CreateBy']
        datadate['Supporter'] = dataForm['Supporter']
        datadate['SubID'] = dataForm['SubSupporter']
        delete dataForm.AppointmentDate;
        delete dataForm.isChange;
        delete dataForm.Department;
        delete dataForm.Timezone;
        delete dataForm.client;
        delete dataForm.Grouppoints;
        delete dataForm.POP;
        var data = {'datapost': dataForm, 'datadate': datadate, '_token': $('input[name=_token]').val()};
        //Xử lý ajax 
        $.ajax({
            url: url,
            cache: false,
            type: "POST",
            dataType: "JSON",
//                data: {'_token': $('input[name=_token]').val()},
            data: data,
            success: function (response) {
                alert(response.msg)
                $('#formChecklistAction').modal('hide');
            },
            error: function (response) {
                if (response.code == 800)
                {
                    alert(response.msg);
                } else {
                    alert((lang == 'vi') ?'Lỗi hệ thống' : 'Error System');
                }
                console.log(response);
            }
        });
    }
})

/* Kết thúc xử lý checklist
 */

