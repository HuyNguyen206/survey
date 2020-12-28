/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/* Xử lý Prechecklist
 */
$(document).bind("ajaxSend", function () {
    $('#loading-image').css('display', 'inline-block');
}).bind("ajaxComplete", function () {
    $('#loading-image').css('display', 'none');
});
//Hiển thị thông tin Prechecklist
$('#createPCL').click(function () {
    $(".modal-body-Prechecklist").html('');
    $('#formPreChecklist').modal('show');
    //Lấy thông tin Prechecklist
    var url = API_URL + "checklist/getPreCheckList";
    $.ajax({
        url: url,
        cache: false,
        type: "POST",
        dataType: "JSON",
        data: {'ObjID': $('input[name=objID]').val(), 'Contract': $('input[name=contractNum]').val(), '_token': $('input[name=_token]').val()},
        success: function (response) {
          
            var tableContent = (lang == 'vi') ? '<tr><th>STT</th><th>ID Prechecklist</th><th>ID hợp đồng</th><th>Số hợp đồng</th><th>Loại khách hàng VIP</th><th>Người liên hệ</th><th>Tình trạng</th><th>Thông tin ghi nhận</th><th>Số lần hỗ trợ</th><th>Tình trạng</th><th>NV ghi nhận</th><th>Thời gian ghi nhận</th><th>Thời gian xử lý</th><th>Thời gian hẹn</th>'
                    + '<th>Tổng số phút</th><th>Có Checklist hay không(0: không có checklist; 1: có checklist) </th><th>Nguồn tạo </th><th>Ghi chú</th></tr>'
            : '<tr><th>STT</th><th>ID Prechecklist</th><th>ID contract</th><th>Contract Number</th><th>Type of VIP Customer </th><th>Contact Person</th><th>Status</th><th>Information</th><th>Quantity support</th><th>Status</th><th>User</th><th>Date</th><th>Date process</th><th>Date Appointment</th>'
                    + '<th>Total minute</th><th>Has CL(0: has none checklist; 1: has checklist) </th><th>Source create </th><th>Note</th></tr>',
                    colorTable, i = 1;
            //Co dữ liệu PreCl
             if(response.hasOwnProperty('data'))
         {
            $.each(response.data, function (index, value) {
                if (value.onCom == false)
                {
                    finnish = false;
                    colorTable = 'white';
                } else
                    colorTable = 'springgreen';
                tableContent += '<tr style="' + colorTable + '"><td>' + i + '</td><td>' + value.ID + '</td><td>' + value.ObjID + '</td><td>' + value.Contract + '</td><td>' + value.TypeVIP + '</td><td>' + value.Location_Name + '</td><td>' + value.Status + '</td><td>' + value.SupDescription + '</td><td>' + value.CountSup + '</td><td>' + value.SupStatus + '</td><td>' + value.CreateBy
                        + '</td><td>' + value.CreateDate + '</td><td>' + value.UpdateDate + '</td><td>' + value.AppointmentTimer + '</td><td>' + value.TotalMinute + '</td><td>' + value.Checklist + '</td><td>' + value.InputType + '</td><td>' + value.Description + '</td></tr>';
                i++
            });
        }
        else
        {
            tableContent= ((lang == 'vi') ?'Không có dữ liệu' : 'No data' )
        }
            $('.modal-body-Prechecklist').html(tableContent);
        },
        error: function (response) {
            if (response.code == 800)
            {
                alert(response.msg);
            } else {
                alert(((lang == 'vi') ?'Lỗi hệ thống' : 'System error'));
            }
            console.log(response);
        }
    });

})

//Kiểm tra còn checklist tồn hay không
$('#createPCLButton').click(function () {
    dataReponse = {};
    $.ajax({
        url: API_URL + "checklist/SupportListCheck",
        cache: false,
        type: "POST",
        dataType: "JSON",
//                data: {'_token': $('input[name=_token]').val()},
        data: {'ObjID': $('input[name=objID]').val(), '_token': $('input[name=_token]').val()},
        success: function (response) {
            if (response.code == 200)
            {
                if (response.hasClStore == true)
                {
                    alert((lang == 'vi') ? 'Hợp đồng này còn checklist tồn' : 'This contract had stored checklist');
                } else
                {
                    var url = API_URL + "checklist/GetFirstStatusName";
                    $.ajax({
                        url: url,
                        cache: false,
                        type: "GET",
                        dataType: "JSON",
                        success: function (response) {
                            console.log(response);
                            var nameUser, listContent = '<option selected disabled>--Chọn--</option>';
                            nameUser = response.name;
                            dataReponse['nameUser'] = nameUser;
                            $('input[name=CreateBy]').val(nameUser);
                            $('input[name=ObjID]').val($('input[name=objID]').val());
                            $.each(response.listFirtStatus, function (index, value) {
                                listContent += ' <option value="' + value.ID + '">' + value.Name + '</option>';
                            });
                            $('#listFirstStatus').html(listContent);
                        },
                        error: function (response) {
                            console.log(response);
                            alert((lang == 'vi') ? 'Lỗi hệ thống' : 'Error system');
                        }
                    });
                    $('#formPreChecklist').modal('hide');
                    $('#contactflagPCL').css('display', 'none')
                    $('#phoneflagPCL').css('display', 'none')
                    $('#incidentflagPCL').css('display', 'none')
                    $('#departmentflagPCL').css('display', 'none')
                    $('#noteflagPCL').css('display', 'none')
                    $('#assignflagPCL').css('display', 'none')
                    $('#createPCLButtonNow').css('display', 'inline-block')
                    $('#formPreChecklistAction2')[0].reset();
                    $('#formPreChecklistAction').modal('show');
                }
            } else if (response.code == 500)
            {
                alert(response.msg)
            } else
                alert((lang == 'vi') ? 'Lỗi hệ thống' : 'Error system');
        },
        error: function (response) {
            if (response.code == 800)
            {
                alert(response.msg)
            } else {
                alert((lang == 'vi') ? 'Lỗi hệ thống' : 'Error system');
            }
        }
    });


});




//Tạo Precl
$('#createPCLButtonNow').click(function () {
//              console.log($scope.CL)
    var msg = '', nameForm = '#formPreChecklistAction';
    if ($(nameForm + ' input[name=Location_Name]').val() == null || $(nameForm + ' input[name=Location_Name]').val() == '')
    {
        msg += '\n Vui lòng nhập người liên hệ';
        $('#contactflagPCL').css('display', 'inline-block')
    } else
    {
        $('#contactflagPCL').css('display', 'none')
    }
    if ($(nameForm + ' input[name=Location_Phone]').val() == null || $(nameForm + ' input[name=Location_Phone]').val() == '')
    {
        msg += ' \n Vui lòng nhập số điện thoại người liên hệ';
        $('#phoneflagPCL').css('display', 'inline-block')
    } else
    {
        $('#phoneflagPCL').css('display', 'none')
    }
    if ($(nameForm + ' select[name=FirstStatus]').val() == null)
    {
        msg += ' \n Vui lòng chọn sự cố ban đầu';
        $('#incidentflagPCL').css('display', 'inline-block')
    } else
    {
        $('#incidentflagPCL').css('display', 'none')
    }

    if ($(nameForm + ' textarea[name=Description]').val() == null || $(nameForm + ' textarea[name=Description]').val() == '')
    {
        msg += ' \n Vui lòng nhập ghi chú';
        $('#noteflagPCL').css('display', 'inline-block')
    } else
    {
        $('#noteflagPCL').css('display', 'none')
    }
    if (msg == '')
    {
        //giả lập cl tự động
//       dataReponse['resultCode'] = 1;
        var input = {};
        $.each($('#formPreChecklistAction2').serializeArray(), function (i, field) {
            input[field.name] = field.value;
        });

        var data = {input: input, contractNum: $('input[name=contractNum]').val(), typeSurvey: $('input[name=typeSurvey]').val(), codedm: $('input[name=codedm]').val(), _token: $('input[name=_token]').val()};
        $('#createPCLButtonNow').css('display', 'none')

        var url = API_URL + "checklist/createPCL";
        $.ajax({
            url: url,
            cache: false,
            type: "POST",
            dataType: "JSON",
            data: data,
            success: function (response) {
                alert(response.msg)
                //Tạo PCL thành công
                $('#formPreChecklistAction').modal('hide');
            },
            error: function (response) {
                alert((lang == 'vi') ? 'Lỗi hệ thống' : 'Error system');
                console.log(response);
            }
        });


    }
})

/*Kết thúc xử lý Prechecklist
 */