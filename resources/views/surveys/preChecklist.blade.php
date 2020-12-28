<?php $transFile = 'preCL'; ?>
<!--Form xử lý Prechecklist-->
        <div class="modal fade" id="formPreChecklist" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="width: 992px;text-align: center;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">{{trans($transFile.'.CreatePreChecklist')}}</h3>
                    </div>
                     <h4>{{trans($transFile.'.PreChecklistList')}}</h4>
                    <div style="    overflow-y: scroll;
                         max-height: 500px;padding: 10px;">
                        <table class="modal-body-Prechecklist" border='1' cellspacing="2" style="text-align: center;width: 100%;font-size: 14px" >
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans($transFile.'.Close')}}</button>
                        <button type="button" class="btn btn-primary" id="createPCLButton">{{trans($transFile.'.CreatePreChecklist')}}</button>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="modal fade" id="formPreChecklistAction" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="width: 992px;text-align: center;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">{{trans($transFile.'.CreatePreChecklist')}}</h3>
                    </div>

               <form id="formPreChecklistAction2">
            <div class="modal-body-Prechecklist-action" style="overflow-y: scroll;text-align: left; max-height: 500px;">
                <div class="col-xs-12"  style="margin-top: 11px;">
                    <div class="col-xs-3">
                        {{trans($transFile.'.CreateUser')}}:
                    </div>
                    <div class="col-xs-7">
                        <input readonly="true" dissize="50" required  name="CreateBy" placeholder="{{trans($transFile.'.CreateUser')}}">
                    </div>
                </div>
                <div class="col-xs-12"  style="margin-top: 11px;">
                    <div class="col-xs-3">
                        {{trans($transFile.'.ContactUser')}}:
                    </div>
                    <div class="col-xs-7">
                        <input size="50" required ng-model="CL.Location_Name" name="Location_Name" placeholder="{{trans($transFile.'.ContactUser')}}">
                    </div>
                    <div style="color: red" class="col-xs-offset-3 col-xs-7 row" id="contactflagPCL" ng-show="contactflag == 1 && !CL.Location_Name" ng-init="contactflag = 0">{{trans($transFile.'.PleaseFillTheNameOfContactUser')}}</div>
                </div>
                <div class="col-xs-12" style="margin-top: 16px;">
                    <div class="col-xs-3">
                        {{trans($transFile.'.ContactUserPhone')}}:
                    </div>
                    <div class="col-xs-7">
                        <input size="50" required ng-model="CL.Location_Phone" name="Location_Phone" placeholder="{{trans($transFile.'.ContactUserPhone')}}">
                    </div>
                    <div style="color: red" class="col-xs-offset-3 col-xs-7 row" id="phoneflagPCL" ng-show="phoneflag == 1 && !CL.Location_Phone" ng-init="phoneflag = 0">{{trans($transFile.'.PleaseFillThePhoneOfContactUser')}}</div>
                </div>
                <div class="col-xs-12" style="margin-top: 16px;">
                    <div class="col-xs-3" style="padding-left: 16px;">{{trans($transFile.'.TakeNoteInitStatus')}}</div>
                    <div class="col-xs-7">
                        <select id="listFirstStatus" style='padding: 0px;margin: 0px; margin-top: -4px;' name="FirstStatus" id="FirstStatusPCL" ng-model="CL.FirstStatus" ng-change="checkInputChecklist()" placeholder="{{trans($transFile.'.ChooseInitStatus')}}..." required>
                        </select>
                    </div>
                    <div style="color: red" class="col-xs-offset-3 col-xs-7 row" id="incidentflagPCL" ng-show="incidentflag == 1 && !CL.FirstStatus" ng-init="incidentflag = 0">{{trans($transFile.'.PleaseChooseInitStatus')}}</div>
                </div>
                <div class="col-xs-12" style="margin-top: 15px">
                    <div class="col-xs-3">{{trans($transFile.'.Note')}}</div>
                    <div class="col-xs-7">
                        <textarea style="width: 541px" placeholder="{{trans($transFile.'.Note')}}" name="Description" ng-model="CL.Description"></textarea>
                    </div>
                    <div style="color: red" class="col-xs-offset-3 col-xs-7 row" id="noteflagPCL" ng-show="noteflag == 1 && !CL.Description" ng-init="noteflag = 0">{{trans($transFile.'.PleaseTakeNote')}}</div>
                </div>
          
                <input type="hidden" name="ObjID" >
            </div>
               </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans($transFile.'.Close')}}</button>
                        <button type="button" class="btn btn-primary" id="createPCLButtonNow">{{trans($transFile.'.Create')}}</button>
                    </div>
                     
                </div>
            </div>
        </div>
       
       
        <!--Kết thúc form xử lý Prechecklist-->
           <script>
            $(document).ready(function () {
            /*
             * Xử lý PreChecklist
             */
            $.getScript(API_URL + "assets/js/actionResolve/preChecklist.js", function(data, textStatus, jqxhr) {
            //        console.log( data ); // Data returned
            console.log(textStatus); // Success
            console.log(jqxhr.status); // 200
            console.log("Load was performed.");
            });
            })

        </script>
        