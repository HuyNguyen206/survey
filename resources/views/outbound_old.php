<?php

use Illuminate\Support\Facades\Auth;

$user = Auth::user();
?>
<html lang="en-US" ng-app="outbound">
    <head>
        <title>Surveys</title>
        <!-- Load Bootstrap CSS -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
              rel="stylesheet">
        <link rel="stylesheet" type="text/css"
              href="assets/outboundapp/bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css"
              href="assets/outboundapp/font-awesome/css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css"
              href="assets/outboundapp/css/styles.css" />
        <link rel="stylesheet" type="text/css"
              href="assets/outboundapp/css/tooltipster.css" />
        <link rel="stylesheet" type="text/css"
              href="assets/outboundapp/css/angular-material.min.css" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    </head>
    <body id="body-page" >
        <div  ng-controller="accountController">
            <md-sidenav class="md-sidenav-left md-whiteframe-z2"
                        md-component-id="left">
                <md-toolbar layout="row">
                    <div class="md-toolbar-tools">
                        <h2>
                            <span>Menu chức năng</span>
                        </h2>
                        <span flex></span>
                        <md-button class="md-icon-button" aria-label="Close Side Panel" ng-click="closeSideNavPanel()">
                            <md-tooltip>Đóng</md-tooltip>
                            <md-icon class="md-default-theme" class="material-icons">&#xE5CD;</md-icon>
                        </md-button>
                    </div>
                </md-toolbar> 
                <md-content layout-padding="">
                    <md-list>
                        <md-subheader class="md-no-sticky">Khảo sát</md-subheader>
                        <md-list-item>
                            <md-icon class="md-default-theme" class="material-icons">&#xE0C9;</md-icon>
                            <p><a ui-sref="survey/inputcontract">Tạo khảo sát</a></p>

                            <!-- <div class="md-secondary">2</div> -->
                        </md-list-item>
                        <md-list-item>
                            <md-icon class="md-default-theme" class="material-icons">&#xE878;</md-icon>
                            <p><a href="/surveys/history-user">Lịch sử khảo sát</a></p>
                            <!-- <md-icon class="md-secondary">2</md-icon>-->
                        </md-list-item>
                        <md-list-item>
                            <md-icon class="md-default-theme" class="material-icons">&#xE251;</md-icon>
                            <p>Báo cáo thống kê</p>
                            <!--  <md-icon class="md-secondary">2</md-icon> -->
                        </md-list-item>
                        <md-divider></md-divider>
                        <md-subheader class="md-no-sticky">Tài khoản</md-subheader>
                        <md-list-item>
                            <md-icon class="md-default-theme" class="material-icons">&#xE851;</md-icon>
                            <p>Đổi mật khẩu</p>
                            <!-- <div class="md-secondary">2</div> -->
                        </md-list-item>
                        <md-list-item>
                            <md-icon class="md-default-theme" class="material-icons">&#xE87C;</md-icon>
                            <p>Thay đổi thông tin cá nhân</p>
                            <!-- <div class="md-secondary">20+</div> -->
                        </md-list-item>
                        <md-list-item>
                            <md-icon class="md-default-theme" class="material-icons">&#xE87C;</md-icon>
                            <p><a ng-href="/logout" aria-label="Default Link" 
                            href="/logout"><span class="ng-scope">Đăng xuất</span>
                            </a>
                            </p>
                            <!-- <div class="md-secondary">20+</div> -->
                        </md-list-item>
                    </md-list>
                </md-content> 
            </md-sidenav>
            <md-button class="md-icon-button md-primary md-fab md-fab-top-left md-button md-ink-ripple" aria-label="Side Panel"
                       ng-click="openSideNavPanel()">
                <md-tooltip>Menu chức năng</md-tooltip>
                <md-icon class="md-default-theme" class="material-icons">&#xE5D2;</md-icon>
            </md-button>
            <!-- Container #4 -->
            <div ui-view></div>

            <md-content flex id="content">
                <div class="box-content">
                    <div class="col-xs-12 none-pad">
                        <!--table-->
                        <div class="table-form">
                            <div class="col-xs-12 col-md-6" style="padding-right: 0px;">
                                <table class="table table-me" cellspacing="0">
                                    <tr >
                                        <td style="width: 150px">Khách hàng
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.CustomerName" >
                                            </div></td>

                                        <td>CMND
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Passport" >
                                            </div>
                                        </td>
                                        <td>Ngày tháng năm sinh
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Birthday">
                                            </div></td>
                                        <td style="text-align: center;">Giới tính
                                            <form style="position: relative;left: 11px;" name="radio-survey-gender" class="radio-survey"
                                                  ng-init="survey.gender">
                                                <md-radio-group ng-model="survey.gender = '1'">
                                                    <div class="col-xs-5" style="padding-left: 12px;">
                                                        <md-radio-button class="md-primary" value="1">Nam</md-radio-button>
                                                    </div>
                                                    <div class="col-xs-5" style="padding-left: 11px;">
                                                        <md-radio-button value="0">Nữ</md-radio-button>
                                                    </div>
                                                </md-radio-group>

                                            </form></td>
                                    </tr>

                                    <tr>
                                        <td>Công ty
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.CompanyName">
                                            </div>
                                        </td>
                                        <td>Email
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Email">
                                            </div>
                                        </td>
                                        <td>Phone
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Phone">
                                            </div>
                                        </td>
                                        <td style="text-align: center">DV sử dụng<form style="position: relative; left: 11px;margin-bottom: -2px;"name="radio-survey-service" class="radio-survey">
                                                <md-checkbox name="paytv"  value="paytv">
                                                    Truyền hình
                                                </md-checkbox>
                                                <md-checkbox name="isp"  value="isp">
                                                    Internet
                                                </md-checkbox>

                                            </form></td>

                                    </tr>
                                    <tr>
                                        <td colspan="2">Địa chỉ trên CMND
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.ObjAddress">
                                            </div>
                                        </td>
                                        <td colspan="2">Địa chỉ lắp đặt
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Address">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  colspan="2">Địa chỉ thanh toán
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.BillTo">
                                            </div>
                                        </td>
                                        <td>Hình thức thanh toán
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.typePay">
                                            </div>
                                        </td>
                                        <td>Gói tính cước
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.FeeLocalType">
                                            </div>
                                        </td>
                                    </tr>



                                </table>
                            </div>

                            <div class="col-xs-12 col-md-6">
                                <table class="table table-me">
                                    <tr>
                                        <td>Số HĐ
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.ContractNum">

                                            </div>

                                        </td>
                                        <td>Ngày tạo HĐ
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.ContractDate">

                                            </div>

                                        </td>
                                        <td>Loại HĐ
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.ContractTypeName" value=""> <input
                                                       type="hidden" ng-init="account.objid = -1"
                                                       ng-mode="account.objid"> <input type="hidden"
                                                       ng-init="account.time_start = -1"
                                                       ng-mode="account.time_start">
                                            </div>
                                        </td>
                                        <td>Tình trạng
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.ContractStatusName" value="">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>

                                    </tr>
                                    <tr>
                                        <td>Loại KHG
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.LegalEntityName">
                                            </div>
                                        </td>
                                        <td>Vùng
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Region">
                                            </div>
                                        </td>
                                        <td>Chi nhánh
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.BranchCode
                                                       ">
                                            </div>
                                        </td> 
                                        <td>Tên truy cập
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.UserName">
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Acc Sale bán
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.AccountSale">
                                            </div>
                                        </td>
                                        <td>Acc triển khai
                                            <div class="form-group">

                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.Constructor">
                                            </div>
                                        </td>
                                        <td>Acc TIN/PNC bảo trì
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.AccountListTIN">
                                            </div>
                                        </td>
                                        <td>Acc INDO bảo trì
                                            <div class="form-group">
                                                <input class="form-control" id="inputdefault" type="text"
                                                       ng-model="account.AccountListINDO">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"><md-button style="font-size: 12px;" ng-click="saveAccount()"
                                                               class="md-raised md-warn" data-dismiss="modal">Cập nhật thông
                                        tin khách hàng</md-button></td>
                                    </tr>



                                </table>
                            </div>
                        </div>
                        <!--table-->
                        <!--combobox-->
                        <!-- Lịch sử hỗ trợ -->
                        <div class="col-xs-12 none-pad">
                            <div class="col-xs-12 wrapbox">
                                <h3 class="header-h3-me">Lịch sử hỗ trợ</h3>
                                <div class="col-xs-12  col-md-12 none-pad"
                                     style="padding-right: 0px;">
                                    <table border="1" class="table table-striped"
                                           ng-repeat="h in history| filter:query as filtered">
                                        <tr>
                                            <td rowspan="2"><strong>{{$index + 1}}</strong></td>
                                            <td>Acc Nhân viên Thực hiện cuộc gọi:</td>
                                            <td class="content"><strong>{{h.HelpdeskName}}</strong></td>
                                            <td>Bắt đầu</td>
                                            <td class="content"><strong>{{h.StartDate}}</strong></td>
                                            <td>Kết thúc</td>
                                            <td class="content"><strong>{{h.EndDate}}</strong></td>
                                            <td>Khách hàng được liên hệ</td>
                                            <td class="content"><strong>{{h.ContactName}}</strong></td>
                                        </tr>
                                        <tr>

                                            <td style="text-align: left;" colspan="8">Thông tin >>
                                                {{h.SupportInfo}}</td>
                                        </tr>

                                    </table>
                                </div>
                                <!-- end lịch sử hỗ trợ -->

                                <!-- end surveys -->
                            </div>
                            <div class="col-xs-12 wrapbox">
                                <h3 class="header-h3-me">Lịch sử khảo sát</h3>
                                <div class="col-xs-12  col-md-12 none-pad"
                                     style="padding-right: 0px;">
                                    <table border="1" class="table table-striped"
                                           ng-repeat="s in surveyhistory">
                                        <tr>
                                            <td rowspan="2"><strong>{{$index + 1}}</strong></td>
                                            <td>Acc Nhân viên Khảo sát:</td>
                                            <td class="content"><strong>{{s.name}}</strong></td>
                                            <td>Bắt đầu</td>
                                            <td class="content"><strong>{{s.section_time_start}}</strong></td>
                                            <td>Kết thúc</td>
                                            <td class="content"><strong>{{s.section_time_completed}}</strong></td>
                                            <td>Nội dung khảo sát</td>
                                            <td class="content"><strong>{{s.survey_title}}</strong></td>
                                        </tr>
                                        <tr>

                                            <td style="text-align: left;" colspan="8">Ghi chú >>
                                                {{s.section_note}}</td>
                                        </tr>

                                    </table>
                                </div>
                                <!-- end lịch sử hỗ trợ -->

                                <!-- end surveys -->
                            </div>
                            
                            <div class="col-xs-12 wrapbox pdd-wrap">
                                <!--radio-->
                                <div class="box-info">
                                    <div class="none-pad col-xs-12  col-md-12">
                                        <label class="col-xs-1" style="padding: 0px;">Kết quả liên hệ </label>
                                        <div class="col-xs-11 none-pad">
                                            <form name="radio-survey-1" ng-init="survey.connected"
                                                  class="radio-survey" ng-init="account.survey.connected = 2">
                                                <md-radio-group ng-model="survey.connected" class="result-contact">
                                                    <div class="col-xs-2">
                                                        <md-radio-button class="md-primary show-box3" value="4">Gặp
                                                            người SD</md-radio-button>
                                                    </div>
                                                    <div class="col-xs-3">
                                                        <md-radio-button class="hide-box3" value="3"> Không gặp người
                                                            SD</md-radio-button>
                                                    </div>
                                                    <div class="col-xs-3">
                                                        <md-radio-button class="hide-box3" value="2"> Gặp KH,KH từ
                                                            chối CS</md-radio-button>
                                                    </div>
                                                    <div class="col-xs-2" style="padding: 0px;">
                                                        <md-radio-button class="hide-box3" value="1"> Không liên lạc
                                                            được</md-radio-button>
                                                    </div>
                                                    <div class="col-xs-2" style="padding: 0px;">
                                                        <md-radio-button class="hide-box3" value="0"> Không cần liên
                                                            hệ </md-radio-button>
                                                    </div>

                                                </md-radio-group>


                                            </form>
                                        </div>
                                    </div>

                                </div>
                                <div class="box-select-box form-horizontal"
                                     style="padding-bottom: 10px; display: none;; width: 100%;">
                                    <div class="none-pad btn-group col-xs-12"
                                         style="padding-right: 0px">
                                        <div class="col-xs-1" style=" padding: 0px;">
                                            <label for="sel1" class=" control-label">Chọn khảo sát</label>
                                        </div>
                                        <div class="col-xs-6">
                                            <form name="radio-survey-1" class="radio-survey type-survey"
                                                  ng-init="survey.id">
                                                <md-radio-group ng-model="survey.id"
                                                                ng-change='choosesurvey()'>
                                                    <div class="col-xs-4">
                                                        <md-radio-button class="md-primary group-question" value="1">Sau
                                                            triển khai</md-radio-button>
                                                    </div>
                                                    <div class="col-xs-4" style="padding: 0px;
                                                         ">
                                                        <md-radio-button value="2" class="group-question"> Sau bảo
                                                            trì</md-radio-button>
                                                    </div>
                                                </md-radio-group>

                                            </form>

                                        </div>
                                        <div class="col-xs-5"
                                             style="text-align: right; padding-right: 0px;">
                                            <md-button style="cursor: context-menu;font-size: 12px;"
                                                       class="md-raised md-primary"
                                                       ng-mouseover="pointUserGuide($event)">Hướng dẫn chấm điểm</md-button>
                                        </div>
                                    </div>
                                </div>
                                <!--/-->
                                <!--/-->
                                <div class="box-check-multi">
                                    <div class="table-form" id="cnt1">

                                        <div class="surveycontent" id="surveycontentID">

                                            {{surveycontentID}}</div>
                                    </div>
                                </div>
                                <div class="panel-footer col-xs-12">
                                    <div class="col-xs-6" style="padding: 0px;">
                                        <div class="col-xs-1" style="padding: 0px;"><span class="question-title">Ghi chú</span></div>
                                        <div class="col-xs-5" style="    width: 40%;">
                                            <textarea ng-model="survey.note" class="note-survey" style="width:250px;"></textarea>
                                        </div>
                                        <div class="col-xs-1" style="padding: 0px;padding-left: 23px;width: 16%;"><span class="question-title">Người liên hệ</span></div>
                                        <div class="col-xs-5" style="width: 34.666667%;">
                                            <textarea ng-model="survey.contact" class="note-survey" style="width:250px;"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <!--<div style="padding: 5px 0px;" class="row">-->
                                        <div class="col-xs-3" style="padding-left: 57px;"><span class="question-title">Xử lý</span></div>
                                        <div class="none-pad col-xs-9" style="padding: 0px">
                                            <div name="radio-survey-2" ng-init="survey.action"
                                                 class="radio-survey col-xs-12">
                                                <md-radio-group ng-model="survey.action">
                                                    <div class="row col-xs-12">
                                                        <div class="row col-xs-12">
                                                            <div class="col-xs-6">
                                                                <md-radio-button value="1"> Không làm gì</md-radio-button>
                                                            </div>
                                                            <div class="col-xs-6">
                                                                <md-radio-button value="2"> Tạo Checklist</md-radio-button>
                                                            </div>
                                                        </div>
                                                        <div class="row col-xs-12">
                                                            <div class="col-xs-6">
                                                                <md-radio-button value="3"> Tạo checklist INDO</md-radio-button>
                                                            </div>
                                                            <div class="col-xs-6">
                                                                <md-radio-button value="4"> Chuyển phòng ban khác</md-radio-button>
                                                            </div>
                                                        </div>


                                                    </div>
                                                </md-radio-group>
                                            </div>
                                        </div>
                                        <!--</div>--> 
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-3"></div>
                                    </div>
                                    <div class="col-xs-12" style="text-align: right; padding: 0px;">
                                        <md-button style="font-size: 12px;" class="md-primary md-raised"
                                                   ng-click="showConfirmcomplete($event)"> Hoàn thành khảo sát<span
                                                class="fa fa-send"></span> </md-button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Satisfaction Survey - END -->

                </div>

                <!-- Modal -->
                <div class="modal fade" id="myModaldialog" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Khảo sát khách hàng</h4>
                            </div>
                            <div class="modal-body">
                                <md-input-container> <label>Nhập số hợp đồng khách hàng</label> <input
                                        size="50" required ng-model="sohd" ng-enter="getInfoAccount()">
                                    <div ng-message="required" role="alert">{{searchStatus}}</div>
                                </md-input-container>


                            </div>
                            <div class="modal-footer">
                                <md-button ng-click="getInfoAccount()"
                                           class="md-raised md-primary"> Xác nhận</md-button>
                                <!--  data-dismiss="modal"  -->
                            </div>
                        </div>

                    </div>
                </div>

        </div>
    </md-content>
    <!-- Load Javascript Libraries (AngularJS, JQuery, Bootstrap) -->
    <script src="assets/outboundapp/lib/angular1.4.8/angular.min.js"></script>
    <script
    src="assets/outboundapp/lib/angular1.4.8/angular-animate.min.js"></script>
    <script src="assets/outboundapp/lib/angular1.4.8/angular-aria.min.js"></script>
    <script
    src="assets/outboundapp/lib/angular1.4.8/angular-messages.min.js"></script>
    <script
    src="assets/outboundapp/lib/angular1.4.8/angular-material.min.js"></script>
    <script
    src="assets/outboundapp/lib/angular1.4.8/angular-ui-router.min.js"></script>


    <script src="assets/outboundapp/js/jquery.min.js"></script>
    <script src="assets/outboundapp/js/bootstrap.min.js"></script>
    <script src="assets/outboundapp/js/jquery.tooltipster.js"></script>

    <!-- AngularJS Application Scripts -->
    <script src="assets/outboundapp/app.js"></script>
    <!-- <script src="assets/outboundapp/controllers/employees"></script> -->
    <script src="assets/outboundapp/controllers/surveys.js"></script>
    <script src="assets/outboundapp/controllers/account.js"></script>
    <script>
                                $('.hide-box3').click(function () {
                                    $('.box-select-box').css('display', 'none');
                                    $('.box-check-multi').css('display', 'none');
                                })
                                $('.show-box3').click(function () {
                                    $('.box-select-box').css('display', 'inline-block');
                                    $('.box-check-multi').css('display', 'inline-block');
                                })

    </script>
</div>
</body>
</html>