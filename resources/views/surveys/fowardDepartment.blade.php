       
 
        <!--Form xử lý chuyển phòng ban-->
        <div class="modal fade" id="formFD" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="width: 992px;text-align: center;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Chuyển phòng ban</h3>
                    </div>
                 
                    
                    
                    
               <form id="formFD2">
            <div class="modal-body-FD" style="    overflow-y: scroll;text-align: left;
                         max-height: 500px;">
      <div class="col-xs-12">
            <div class="col-xs-3" style="    margin-top: 10px;padding-left: 31px;">
                Phòng ban chuyển tiếp 
            </div>
            <div class="col-xs-5">
                <select class="reason-menu"  name="department" ng-model="department" ng-init="department=6" placeholder="Chọn phòng ban chuyển tiếp..." >
                    <option disabled >--Chọn--</option>
                    <option value="1">IBB</option>
                    <option value="2">TIN/PNC</option>
                    <option value="3">Telesale</option>
                    <option value="4">CUS</option>
                    <option value="5">CS chi nhánh</option>
                    <option value="6" selected>CSHO </option>
                    <option value="7">KDDA </option>
                    <option value="8">NVTC</option>
                </select>
            </div>
            <div  class="col-xs-12">
                <span style="color: red" id="departmentTranfer" ng-show="departmentTranfer" ng-init="departmentTranfer = false">Chưa chọn phòng ban</span>
            </div>

            <div class="col-xs-12  block-department">
                <div class="col-xs-12" >
                    <div class="col-xs-3">Chuyển tiếp IBB</div>
                    <div class="col-xs-7"> <input type="checkbox"  ng-model="ibb.heck" name="ibbCheck"  class="survey-question" value="1"/></div>
                </div>
                <div class="col-xs-12" >
                    <div class="col-xs-3 reason-top" >Lý do</div>

                    <div class="col-xs-7">
                        <select class="reason-menu" name="ibbReason" ng-model="ibb.reason" placeholder="Chọn lý do" required>
                             <option disabled selected>--Chọn--</option>
                            <option value="1">Nhu cau dang ky them dich vu</option>
                            <option value="2">Tu van sai thong tin khuyen mai, goi cuoc</option>
                            <option value="3">Nhu cau phat sinh them (hoa don, thanh ly)</option>
                            <option value="4">Thieu hop dong</option>
                            <option value="5">Sale hua bo sung khuyen mai tra truoc theo yeu cau KH</option>
                            <option value="6">Khac</option>
                        </select>
                    </div>

                </div>
                <div class="col-xs-12">
                    <span style="color: red" id="reasonIbb" ng-show="reasonIbb" ng-init="reasonIbb = false">Chưa chọn lý do</span>
                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" name="ibbDescription" ng-model="ibb.description" name="description" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red" id="descriptionIbb" ng-show="descriptionIbb" ng-init="descriptionIbb = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp TIN/PNC</div>
                    <div class="col-xs-7"> <input type="checkbox" name="tinCheck" ng-model="tin.check"  class="survey-question"  value="2"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <div class="col-xs-3 reason-top">Lý do</div>

                    <div class="col-xs-7">
                        <select class="reason-menu" name="tinReason" ng-model="tinReason" placeholder="Chọn lý do" required>
                             <option disabled selected>--Chọn--</option>
                            <option value="1">Thieu bien ban nghiem thu</option>
                            <option value="2">Sai thiet bi so voi CLKM</option>
                            <option value="3">Khieu nai sau trien khai (Day LAN, huong di cap..)</option>
                            <option value="4">Bao hanh thiet bi tai nha cac truong hop dac biet</option>
                            <option value="5">Yeu cau bao tri</option>
                            <option value="6">Khac</option>
                        </select>
                    </div>

                </div>
                <div class="col-xs-12">
                    <span style="color: red" id="reasonTin" ng-show="reasonTin" ng-init="reasonTin = false">Chưa chọn lý do</span>
                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3 ">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" ng-model="tinDescription" name="tinDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red"  id="descriptionTin" ng-show="descriptionTin" ng-init="descriptionTin = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp TLS</div>
                    <div class="col-xs-7"> <input type="checkbox" name="tlsCheck"  ng-model="tlsCheck"  class="survey-question"  value="3"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <div class="col-xs-3 reason-top " >Lý do</div>

                    <div class="col-xs-7">
                        <select class="reason-menu" name="tlsReason" ng-model="tlsReason" placeholder="Chọn lý do" required>
                             <option disabled selected>--Chọn--</option>
                            <option value="1">Sai cuoc IPTV</option>
                            <option value="2">Sai phi day LAN phat sinh</option>
                            <option value="3">Sai phi HDBox</option>
                            <option value="4">Thac mac cuoc sau nang cap</option>
                            <option value="5">Nhu cau lap them HDBox</option>
                            <option value="6">Khac</option>
                        </select>
                    </div>

                </div>
                <div class="col-xs-12">
                    <span style="color: red" id="reasonTls" ng-show="reasonTls" ng-init="reasonTls = false">Chưa chọn lý do</span>
                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3 ">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" ng-model="tlsDescription" name="tlsDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red" id="descriptionTls" ng-show="descriptionTls" ng-init="descriptionTls = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp CUS</div>
                    <div class="col-xs-7"> <input type="checkbox" ng-model="cusCheck" name="cusCheck"  class="survey-question"  value="4"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <div class="col-xs-3 reason-top">Lý do</div>

                    <div class="col-xs-7">
                        <select class="reason-menu" name="cusReason" ng-model="cusReason" placeholder="Chọn lý do" required>
                            <option disabled selected>--Chọn--</option>
                            <option value="1">Sai CLKM IPTV/Net</option>
                            <option value="2">Sai cap nhat lai hinh thuc thanh toan</option>
                            <option value="3">Sai thong tin KH</option>
                            <option value="5">Sai thong tin lien he</option>
                            <option value="6">Sai thiet bi (khac voi thong tin tren HD)</option>
                            <option value="7">Nhu cau phat sinh them (hoa don, Ip tinh..)</option>
                            <option value="8">Khac</option>
                        </select>
                    </div>

                </div>
                <div class="col-xs-12">
                    <span style="color: red" id="reasonCus" ng-show="reasonCus" ng-init="reasonCus = false">Chưa chọn lý do</span>
                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3  ">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" ng-model="cusDescription" name="cusDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red" id="descriptionCus" ng-show="descriptionCus" ng-init="descriptionCus = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp CSCN</div>
                    <div class="col-xs-7"> <input type="checkbox" name="cscnCheck"  ng-model="cscnCheck"  class="survey-question"  value="5"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <div class="col-xs-3 reason-top">Lý do</div>

                    <div class="col-xs-7">
                        <select class="reason-menu" name="cscnReason" ng-model="cscnReason" placeholder="Chọn lý do" required>
                             <option disabled selected>--Chọn--</option>
                            <option value="1">Nhu cau phat sinh them (hoa don, KN, thac mac cuoc..)</option>
                            <option value="2">Thac mac chinh sach bang thong, dieu chinh uu dai</option>
                            <option value="3">Yeu cau thu tra truoc</option>
                            <option value="4">Khac</option>
                            <option value="5">KH co nhu cau CDV, CDD khong dong y qua quay</option>
                        </select>
                    </div>

                </div>
                <div  class="col-xs-12">
                    <span style="color: red" id="reasonCscn" ng-show="reasonCscn" ng-init="reasonCscn = false">Chưa chọn lý do</span>
                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" ng-model="cscnDescription" name="cscnDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red" id="descriptionCscn" ng-show="descriptionCscn" ng-init="descriptionCscn = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp NVTC</div>
                    <div class="col-xs-7"> <input type="checkbox" name="nvtcCheck" ng-model="nvtcCheck"  class="survey-question"  value="6"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <div class="col-xs-3 reason-top">Lý do</div>

                    <div class="col-xs-7">
                        <select class="reason-menu" name="nvtcReason" ng-model="nvtcReason" placeholder="Chọn lý do" required>
                              <option disabled selected>--Chọn--</option>
                            <option value="1">Xin thong tin lien he</option>
                            <option value="2">Yeu cau thu tra truoc</option>
                            <option value="3">Khac</option>

                        </select>
                    </div>

                </div>
                <div  class="col-xs-12">
                    <span style="color: red" id="reasonNvtc" ng-show="reasonNvtc" ng-init="reasonNvtc = false">Chưa chọn lý do</span>
                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" ng-model="nvtc.description" name="nvtcDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red" id="descriptionNvtc" ng-show="descriptionNvtc" ng-init="descriptionNvtc = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp CSHO </div>
                    <div class="col-xs-7"> <input type="checkbox" name="cshoCheck"  ng-model="csho.check"  class="survey-question"  value="7"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <input type="hidden" name="cshoReason" ng-model="csho.reason" value="0" ng-init="csho.reason = 0">
                    <!--                    <div class="col-xs-2" style="    margin-top: 30px;">Lý do</div>
                    
                                        <div class="col-xs-7">
                                            <md-select name="sel_department" ng-model="ibb.reason" placeholder="Chọn lý do" required>
                                                <md-option ng-value="1">Nhu cau dang ky them dich vu</md-option>
                                                <md-option ng-value="2">Tu van sai thong tin khuyen mai, goi cuoc</md-option>
                                                <md-option ng-value="3">Nhu cau phat sinh them (hoa don, thanh ly)</md-option>
                                                <md-option ng-value="4">Thieu hop dong</md-option>
                                                <md-option ng-value="5">Sale hua bo sung khuyen mai tra truoc theo yeu cau KH</md-option>
                                                <md-option ng-value="6">Khac</md-option>
                                            </md-select>
                                        </div>-->

                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50"  ng-model="cshoDescription" name="cshoDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;"class="col-xs-12">
                    <span style="color: red" id="descriptionCsho" ng-show="descriptionCsho" ng-init="descriptionCsho = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12 block-department" style="margin-top: 10px">
                <div class="col-xs-12">
                    <div class="col-xs-3">Chuyển tiếp KDDA </div>
                    <div class="col-xs-7"> <input type="checkbox" name="kddaCheck" ng-model="kdda.check"  class="survey-question"  value="8"/></md-checkbox></div>
                </div>
                <div class="col-xs-12" >
                    <input type="hidden" name="kddaReason" value="0" ng-model="kdda.reason" ng-init="kdda.reason = 0">
                    <!--                    <div class="col-xs-2" style="    margin-top: 30px;">Lý do</div>
                    
                                        <div class="col-xs-7">
                                            <md-select name="sel_department" ng-model="ibb.reason" placeholder="Chọn lý do" required>
                                                <md-option ng-value="1">Nhu cau dang ky them dich vu</md-option>
                                                <md-option ng-value="2">Tu van sai thong tin khuyen mai, goi cuoc</md-option>
                                                <md-option ng-value="3">Nhu cau phat sinh them (hoa don, thanh ly)</md-option>
                                                <md-option ng-value="4">Thieu hop dong</md-option>
                                                <md-option ng-value="5">Sale hua bo sung khuyen mai tra truoc theo yeu cau KH</md-option>
                                                <md-option ng-value="6">Khac</md-option>
                                            </md-select>
                                        </div>-->

                </div>
                <div class="col-xs-12  note-depart" >
                    <div class="col-xs-3">
                        Ghi chú
                    </div>
                    <div class="col-xs-7"> 
                        <textarea style="    width: 593px;" size="50" ng-model="kdda.description" name="kddaDescription" placeholder="Ghi chú"></textarea>
                    </div>

                </div>
                <div style="    margin-top: -22px;" class="col-xs-12">
                    <span style="color: red" id="descriptionKdda" ng-show="descriptionKdda" ng-init="descriptionKdda = false">Chưa ghi chú</span>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="col-xs-5">
                    <span style="color: red" id="validateWhole" ng-show="validateWhole" ng-init="validateWhole = false">Chưa chọn phòng ban tiếp nhận</span>
                </div>

            </div>
        </div>
            </div>
               </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button id="flagDepartment" type="button" class="btn btn-primary">Chuyển</button>
                    </div>
                     
                </div>
            </div>
        </div>
        <!--Kết thúc form xử lý phòng ban-->
          <script>
            $(document).ready(function () {
            departFlag = '';
            /*
             * Xử lý chuyển phòng ban
             */
            $.getScript(API_URL + "assets/js/actionResolve/fowardDepartment.js", function(data, textStatus, jqxhr) {
            //        console.log( data ); // Data returned
            console.log(textStatus); // Success
            console.log(jqxhr.status); // 200
            console.log("Load was performed.");
            });
            })

        </script>