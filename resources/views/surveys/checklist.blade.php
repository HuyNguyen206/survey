<?php $transFile = 'checklist'; ?>
<!--Form xử lý checklist-->
<div class="modal fade" id="formChecklist" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width: 992px;text-align: center;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">{{trans($transFile.'.CreateChecklist')}}</h3>
            </div>
            <h4>{{trans($transFile.'.ChecklistList')}}</h4>
            <div class="modal-body-checklist" style="overflow-y: scroll;max-height: 500px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans($transFile.'.Close')}}</button>
                <button type="button" class="btn btn-primary" id="createCLButton">{{trans($transFile.'.CreateChecklist')}}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="formChecklistAction" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width: 992px;text-align: center;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">{{trans($transFile.'.CreateChecklist')}}</h3>
            </div>
            <div class="modal-body-checklist-action" style="padding-bottom: 15px; overflow-y: scroll;text-align: left;max-height: 500px;">
                <form id="formChecklistAction2">
                    <div class="md-dialog-content" style="white-space: normal !important;">
                        <div class="col-xs-12 row">
                            <div class="col-xs-3" style="line-height: 34px">
                                {{trans($transFile.'.CreateUserName')}}:
                            </div>
                            <div class="col-xs-7" style="margin-top: 8px;">
                                <input readonly="true" size="50" required  name="CreateBy">
                            </div>

                        </div>
                        <div class="col-xs-12 row" style="margin-top: 45px;">
                            <div class="col-xs-3">{{trans($transFile.'.ChooseErrorType')}}</div>
                            <div class="col-xs-7">
                                <select style='padding: 0px;margin: -10px 0px 4px;' name="Init_Status" id="clInitStatus" ng-model="CL.Init_Status" ng-change="getOwnerType()">
                                   
                                </select>
                            </div>
                            <div style="color: red" class="col-xs-12" id="incidentflag" >{{trans($transFile.'.PleaseChooseErrorType')}}</div>
                        </div>
                        <div class="col-xs-12 row" >
                            <div class="col-xs-3">{{trans($transFile.'.Note')}}</div>
                            <div class="col-xs-7">
                                <textarea name="Description" style="width: 541px" placeholder="{{trans($transFile.'.Note')}}" ng-model="CL.Description" required></textarea>
                            </div>
                            <div style="color: red" class="col-xs-12" id="desflag"  ng-init="desflag = 0">{{trans($transFile.'.PleaseTakeNote')}}</div>
                        </div>
                        <div class="col-xs-12 row">
                            <div class="col-xs-3" style="margin-top: 10px;">{{trans($transFile.'.Partner')}}</div>
                            <div class="col-xs-7">
                                <input readonly="true" size="50" name="Supporter" ng-model="CL.Supporter" title="Gọi store/api" name="contactName" placeholder="{{trans($transFile.'.Partner')}}">
                            </div>
                        </div>
                        <div class="col-xs-12 row">
                            <div class="col-xs-3">{{trans($transFile.'.MaintenanceGroup')}}</div>
                            <div class="col-xs-7">
                                <input readonly="true" size="50" name="SubSupporter" ng-model="CL.SubSupporter" title="Gọi store/api"  name="SubSupporter" placeholder="{{trans($transFile.'.MaintenanceGroup')}}">
                            </div>
                        </div>
                        <div class="col-xs-12 row OwnerTypeFlag"  style="margin-top: 10px;" ng-show="CL.OwnerType == '1'">
                            <div class="col-xs-3">{{trans($transFile.'.AppointmentDate')}}</div>
                            <div class="col-xs-7">
                                <input class="AppointmentDate" size="50" type="text" name="AppointmentDate"  title="{{trans($transFile.'.AppointmentDate')}}">
                            </div>
                        </div> 



                        <div class="col-xs-12 row OwnerTypeFlag" style="line-height: 7px; margin-top: 16px;" ng-show="CL.OwnerType == '1'" >
                            <div class="col-xs-3">
                                {{trans($transFile.'.AppointmentCustomer')}}
                            </div>
                            <div class="col-xs-7">
                                <input type="checkbox" name="client" ng-change="getDateInfo()" id="getDateInfo"  style="width: 17px; height: 17px;" ng-model="client" >
                            </div>

                        </div>
                        <div class="col-xs-12 row OwnerTypeFlag" style="line-height: 7px; margin-top: 16px;padding-left: 31px;" ng-show="CL.OwnerType == '1'">
                            <table  border='1' style=" width: 644px; height: 172px; text-align: center;" ng-show='AP'>
                                <thead>
                                    <tr>
                                        <td>{{trans($transFile.'.Timezone')}}</td>
                                        <td>{{trans($transFile.'.NumberOfCase')}}</td>
                                        <td>{{trans($transFile.'.Capacity')}}</td>
                                    </tr>
                                </thead>
                                <tbody id="skillSchedule">
                                    
                                </tbody>
                            </table>
                        </div>
                        <div style="color: red" class="col-xs-12" id="assignflag"  ng-init="assignflag = 0">{{trans($transFile.'.PleaseChooseTimezoneToChecklist')}}</div>


                        <input type="hidden" name="DeptID" >
                        <input type="hidden" name="ObjId">
                        <input type="hidden"  name="Type"  >
                        <input type="hidden"  name="RequestFrom"  >
                        <input type="hidden"  name="isChange" value="1"  ng-init="AD.isChange = 1">
                        <input type="hidden"  name="Department"  value="1" ng-model="AD.Department" ng-init="AD.Department = 1">
                        <input type="hidden" name="SubAssign" class="col-xs-5" value="0" ng-model="CL.SubAssign" ng-init="CL.SubAssign = '0'" ng-true-value="'1'" ng-false-value="'0'" >
                        <input type="hidden" name="Upgrade"  class=" col-xs-5" value="0" ng-model="CL.Upgrade" ng-init="CL.Upgrade = '0'" ng-true-value="'1'" ng-false-value="'0'" >
                        <input type="hidden" name="CLElectric" class="col-xs-5" value="0" ng-model="CL.bitCLElectric" ng-init="CL.bitCLElectric = 0"    ng-true-value="1" ng-false-value="0" >
                        <input type="hidden" name="Grouppoints" >
                        <input type="hidden" name="POP" >
                        <input type="hidden"  name="ModemType" value="1" >
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans($transFile.'.Close')}}</button>
                <button type="button" class="btn btn-primary" id="createCLButtonNow">{{trans($transFile.'.Create')}}</button>
            </div>

        </div>
    </div>
</div>
<!--Kết thúc form xử lý checklist-->

<script>
    $(document).ready(function () {
        API_URL = window.location.origin + '/';
        invalidValue = [0, '', null, 'null'];
//        departFlag = '';
        //Lấy ngày hiện tại
        window.getCurrentDate = function () {
            //        function getCurrentDate () {
            var fullDate = new Date();
            var twoDigitMonth = fullDate.getMonth() + 1 + "";
            if (twoDigitMonth.length == 1)
                twoDigitMonth = "0" + twoDigitMonth;
            var twoDigitDate = fullDate.getDate() + "";
            if (twoDigitDate.length == 1)
                twoDigitDate = "0" + twoDigitDate;
            var currentDate = fullDate.getFullYear() + "/" + twoDigitMonth + "/" + twoDigitDate;
            return currentDate;
        }

        $(document).bind("ajaxSend", function () {
            $('#loading-image').css('display', 'inline-block');
        }).bind("ajaxComplete", function () {
            $('#loading-image').css('display', 'none');
        });
        /*
         * Xử lý Checklist
         */
        $.getScript(API_URL + "assets/js/actionResolve/checklist.js", function (data, textStatus, jqxhr) {
            //        console.log( data ); // Data returned
            console.log(textStatus); // Success
            console.log(jqxhr.status); // 200
            console.log("Load was performed.");
        });
         });

</script>