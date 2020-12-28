/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
        
        /*Bắt đầu xử lý phòng ban
        */ 
        
    // Hiển thị pop-up chuyển phòng ban
    $('#forwardDepartment').click(function(){
        $('#flagDepartment').css('display','inline-block')
        $('#formFD').modal('show');
        $('#departmentTranfer').css('display','none')
        
        $('#reasonIbb').css('display','none')
        $('#descriptionIbb').css('display','none')
        $('#reasonTin').css('display','none'),
        $('#descriptionTin').css('display','none')
        $('#reasonTls').css('display','none')
        $('#descriptionTls').css('display','none')
        $('#reasonCus').css('display','none')
        $('#descriptionCus').css('display','none')
        $('#reasonCscn').css('display','none')
        $('#descriptionCscn').css('display','none')      
        $('#reasonNvtc').css('display','none')
        $('#descriptionNvtc').css('display','none')
        $('#reasonNvtc').css('display','none')
        $('#descriptionCsho').css('display','none')
        $('#descriptionKdda').css('display','none')
        $('#validateWhole').css('display','none')
        
    })

    /*
     Hàm validate dữ liệu chuyển phòng ban
     */
     function validateDepartment(typeData, arrayValidDepart, reasonName, desName)
        {
            //Hợp lệ thì thêm vào mảng trên
            if ($('select[name='+typeData+'Reason]').val() !== null && $('textarea[name='+typeData+'Description]').val() != null  && $('textarea[name='+typeData+'Description]').val() != '')
            {
//                alert('hop le')
                var groupType = {};
                groupType['check']=$('input[name='+typeData+'Check]').val();
                groupType['description']=$('textarea[name='+typeData+'Description]').val();
                groupType['reason']=(typeData == 'csho' || typeData == 'kdda') ? 0 : $('select[name='+typeData+'Reason]').val();
                arrayValidDepart.push(groupType);
                  $('#'+desName).css('display','none')
                  $('#'+reasonName).css('display','none')
            }
            //Không hợp lệ
            else {
//                  alert('ko hop le')
                if ($('select[name='+typeData+'Reason]').val() == null)
                {
                     $('#'+reasonName).css('display','inline-block')
                     departFlag=false
                } else
                {
                    $('#'+reasonName).css('display','none')
                }
                if ($('textarea[name='+typeData+'Description]').val() == null || $('textarea[name='+typeData+'Description]').val() === '')
                {
                     $('#'+desName).css('display','inline-block')
                     departFlag=false
                } else
                {
                     $('#'+desName).css('display','none')
                }
            }
            return arrayValidDepart
        }

        //Hàm chuyển phòng ban
        $('#flagDepartment').click(function(){
                departFlag = true;
            if ($('select[name=department]').val() == null)
            {
                departFlag = false;
                $('#departmentTranfer').css('display','inline-block')
            }
            //Không tick vào phòng ban nào cả
            else if (!$('input[name=ibbCheck]').is(':checked') && !$('input[name=tinCheck]').is(':checked') && !$('input[name=tlsCheck]').is(':checked') && !$('input[name=cusCheck]').is(':checked') && !$('input[name=cscnCheck]').is(':checked') && !$('input[name=cshoCheck]').is(':checked') && !$('input[name=kddaCheck]').is(':checked') && !$('input[name=nvtcCheck]').is(':checked'))
            {
                departFlag = false;
                $('#validateWhole').css('display','inline-block')

            } else
            {
                //Mảng chứa dữ liệu phòng ban hợp lệ
                var arrayValidDepart = [];
                //Validate IBB nếu tick
                if ($('input[name=ibbCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('ibb', arrayValidDepart, 'reasonIbb', 'descriptionIbb');
                } else {
                     $('#reasonIbb').css('display','none')
                      $('#descriptionIbb').css('display','none')
                }
                //Validate TIN nếu tick
                if ($('input[name=tinCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('tin', arrayValidDepart, 'reasonTin', 'descriptionTin')
                } else {
                    $('#reasonTin').css('display','none')
                    $('#descriptionTin').css('display','none')
                }
                //Validate tls nếu tick
                if ($('input[name=tlsCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('tls', arrayValidDepart, 'reasonTls', 'descriptionTls')
                } else {
                    $('#reasonTls').css('display','none')
                    $('#descriptionTls').css('display','none')
                }
                //Validate cus nếu tick
                if ($('input[name=cusCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('cus', arrayValidDepart, 'reasonCus', 'descriptionCus')
                } else {
                     $('#reasonCus').css('display','none')
                    $('#descriptionCus').css('display','none')
                }
                //Validate cscn nếu tick
                if ($('input[name=cscnCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('cscn', arrayValidDepart, 'reasonCscn', 'descriptionCscn')
                } else {
                     $('#reasonCscn').css('display','none')
                    $('#descriptionCscn').css('display','none')
                }
                //Validate nvtc nếu tick
                if ($('input[name=nvtcCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('nvtc', arrayValidDepart, 'reasonNvtc', 'descriptionNvtc')
                } else {
                    $('#reasonNvtc').css('display','none')
                    $('#descriptionNvtc').css('display','none')
                }
                //Validate csho nếu tick
                if ($('input[name=cshoCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('csho', arrayValidDepart, 'reasonCsho', 'descriptionCsho')
                } else {                     
                    $('#descriptionCsho').css('display','none')
                }
                //Validate kdda nếu tick
                if ($('input[name=kddaCheck]').is(':checked'))
                {
                    arrayValidDepart = validateDepartment('kdda', arrayValidDepart, 'reasonKdda', 'descriptionKdda')
                } else {
                     $('#descriptionKdda').css('display','none')
                }
            }
            //Validate đúng
            if (departFlag == true)
            {
                 $('#flagDepartment').css('display','inline-block')
                console.log(arrayValidDepart);
                var arrDepartment = [], typeSurvey, codedm, contractNum;
                typeSurvey = $('input[name=typeSurvey]').val(); 
                contractNum = $('input[name=contractNum]').val();  
                codedm = $('input[name=codedm]').val(); 
                
                arrDepartment.push({ObjID: $('input[name=objID]').val(), TableID: 1, arrayValidDepart: arrayValidDepart, Department: $('select[name=department]').val(), typeSurvey: typeSurvey, contractNum: contractNum, codedm: codedm});
                console.log(arrDepartment);
                var url = API_URL + "checklist/forwardDepartment";
                $.ajax({
                    url: url,
                    cache: false,
                    type: "POST",
                    dataType: "JSON",
                    data: {'datapost': arrDepartment,  '_token': $('input[name=_token]').val()},
                    success: function (response) {
                    if (response.code == 200)
                    {
                        
                        alert(response.msg.replace(/\\n/g, "\n"))
                    }
                     $('#formFD').modal('hide');
                    //Khóa xử lý không làm gì
                },
                error: function (response) {
                      if  (response.code == 800)
                    {
                        alert(response.msg);
                    } else {
                        alert('Lỗi hệ thống');
                    }
                    console.log(response);
                }
        });
        
            }
        })
//    } 
        /*Kết thúc xử lý phòng ban
        * 
         */ 

