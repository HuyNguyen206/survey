@extends('layouts.app')

@section('content')
    <span us-spinner="{radius:30, width:8, length: 16}"></span>
    <div class="col-xs-12 padding-10" >
        <div class="box-search bg-gray">
            <div class="col-xs-12">
                <div class="col-xs-3">
                    <h4>Ngày bắt đầu</h4>
                    <div class="input-group"
                         moment-picker="filters.startDate"
                         format="YYYY-MM-DD">

    <span class="input-group-addon">
        <i class="octicon octicon-calendar"></i>
    </span>
                        <input style="        height: 35px;" class="form-control"
                               placeholder="Nhập ngày bắt đầu"
                               ng-model="filters.startDate"
                               ng-model-options="{ updateOn: 'blur' }">
                    </div>
                </div>

                <div class="col-xs-3">
                    <h4>Ngày kết thúc</h4>
                    <div class="input-group"
                         moment-picker="filters.endDate"
                         format="YYYY-MM-DD">

    <span class="input-group-addon">
        <i class="octicon octicon-calendar"></i>
    </span>
                        <input  style="        height: 35px;" class="form-control"
                                ng-model="filters.endDate"
                                md-placeholder="Nhập ngày kết thúc"
                                ng-model-options="{ updateOn: 'blur' }">
                    </div>
                </div>

                <div class="col-xs-3">
                    <h4>Loại khảo sát</h4>
                    <multiselect class="input-xlarge" multiple="true"
                                 ng-model="typeSurvey"
                                 options="t.label for t in dataType"
                                 change="selected()" ></multiselect>
                </div>

                <div class="col-xs-3">
                    <h4>Số hợp đồng</h4>
                    <input  style="        height: 35px;" class="form-control"  type="text"
                            ng-model="filters.contract"  >
                </div>
            </div>
            <!--</div>-->
            <div class="col-xs-12">
                <div class="col-xs-3">
                    <h4>Kết quả liên hệ</h4>
                    <!--<div ng-dropdown-multiselect="result" options="data"  selected-model="contactResultf" extra-settings="settings" translation-texts="text" style="cursor: pointer;"></div>-->
                    <multiselect class="input-xlarge" multiple="true"
                                 ng-model="contactResultf"
                                 options="c.label for c in data"
                                 change="selected()" ></multiselect>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12" style="text-align: center">

        <button class="btn-search" ng-click="getSurvey()"  style="float: none"><span class="glyphicon glyphicon-search"></span> Tìm kiếm</button>
    </div>
    <!--</form>-->
    <!--</div>-->
    <div class="bg-gray" style="    margin-top: 10px;
     background-color: white;
     border: none;
     padding-left: 14px;">
        <h5 style="font-weight: bold" ng-bind="total"> </h5>
    </div>
    <div class="table-servey bg-gray" style="margin-top: 10px;" ng-hide="trigger">
        <table style="width: 100%">
            <thead>
            <tr>
                <th>STT</th>
                <th>Số HĐ</th>
                <th>Tên khách hàng</th>
                <th>Tình trạng khảo sát</th>
                <th>Ghi chú</th>
                <th>Xử lý</th>
                <th>Loại khảo sát</th>
                <th>Người khảo sát</th>
                <th>
                    <i class="icon-time bigger-110 hidden-480"></i>
                    Thời gian hoàn thành
                </th>


                <th>Chi tiết</th>
                <th>Sửa - Khảo sát lại</th>

            </tr>
            </thead>
            <tbody>
            <tr ng-show="do == 3"><td colspan="11" style="text-align:center;">Đang tải dữ liệu mới!!</td></tr>
            <!--                            <tr ng-show="users.length > 0&&do==1"><td colspan="11" style="text-align:center;">Đang tải dữ liệu mới!!</td></tr>-->
            <tr ng-show="do == 2"><td colspan="11" style="text-align:center;">Không tìm thấy kết quả!!</td></tr>
            <tr dir-paginate="user in users|filter:q | itemsPerPage: 50" current-page="currentPage" total-items="total_count">
                <td class="text-center">{{$index + 1}}</td>
                <td>{{user.section_contract_num}}</td>
                <td>{{user.section_customer_name}}</td>
                <td><span ng-show='user.section_connected == 4' class='label label-success arrowed'>Gặp người SD</span><span ng-show='user.section_connected == 0' class='label label-warning'>Không cần liên hệ</span><span ng-show='user.section_connected == 1' class='label label-danger arrowed-in'>Không liên lạc được</span><span ng-show='user.section_connected == 2' class='label label-danger arrowed-in'>Gặp KH, KH từ chối CS</span><span ng-show='user.section_connected == 3' class='label label-danger arrowed-in'>Không gặp người SD</span></td>
                <td>{{user.section_note}}</td>
                <td ng-bind="action{{user.section_action}}"></td>
                <td ng-bind="type{{user.section_survey_id}}"></td>
                <td>{{user.section_user_name}}</td>

                <td>{{user.section_time_completed}}</td>
                <td class="text-center"><a href="/#/history/detail/{{user.section_contract_num}}/{{user.section_id}}" title="Chi Tiết"><img src="outboundapp/img/file.png"></a></td>
            <!--                                <td class="hidden-480">

                                                                                                        <?php if($surveySections->section_survey_id > 0) {//chưa gặp người sử dụng  ?>
                                                    <div class="visible-md visible-lg hidden-sm hidden-xs btn-group">
                                                        <a href="/#/history/detail/{{user.section_contract_num}}/{{user.section_id}}" class="open-tooltip" role="button" data-toggle="modal" title="Chi Tiết"><button class="btn btn-xs btn-info"><i class="icon-folder-open bigger-120"></i></button></a>
                                                    </div>
                                                                                                        <?php } ?>
                    </td>-->
                <td><span ng-show="{{ user.edit == 2}}">Quá thời gian cho sửa quy định</span><a href="/#/survey/edit/{{user.section_id}}" ng-show="{{ user.edit == 1}}"><button class="btn btn-xs btn-info"><i class="icon-edit bigger-120"></i></button></a>&nbsp;&nbsp;&nbsp;<span ng-show="{{ user.retry == 2}}">Quá thời gian cho khảo sát lại quy định</span><a href="/#/survey/retry/{{user.section_id}}" ng-show="{{ user.retry == 1}}"><img src="outboundapp/img/return.png"></a></td>
            </tr>
            </tbody>
        </table>
        <dir-pagination-controls
                max-size="10"
                direction-links="true"
                boundary-links="true"
                on-page-change="getData(newPageNumber)" >
        </dir-pagination-controls>
    </div>
    <!--                <div ui-view></div>-->
    <div class="col-xs-12">
        <h5 style="font-weight: bold" ng-bind="total"> </h5>
    </div>
    <div style="padding-top: 20px;"><a class="return" ng-click="returnClick()" href="">Quay về màn hình khảo sát</a>    </div>
    </div>


    <div class="page-content">
    <?php
    $controller = 'history';
    $transfile = $controller;
    $common = 'common';
    $prefix = 'dashboard';
    ?>
    <!-- /.page-header -->
    
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form" method="POST" action="{{ url('/'.$prefix.'/'.$controller.'/detail_survey') }}">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-xs-12">
                        
                        <div class="space-4"></div>

                        <div class="row">
                            <div class="col-xs-12">

                                <div class="table-responsive">
                                    <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{trans($transfile.'.Section Account')}}</th>
                                                <th>{{trans($transfile.'.Section Status')}}</th>
                                                <th>{{trans($transfile.'.Section Note')}}</th>
                                                <th>{{trans($transfile.'.Section Action')}}</th>
                                                <th>{{trans($transfile.'.Section Survey')}}</th>
                                                <th>{{trans($transfile.'.Supporter')}}</th>
                                                <th>
                                                    <i class="icon-time bigger-110 hidden-480"></i>
                                                    {{trans($common.'.Create at')}}
                                                </th>
                                                <th></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($modelSurveySections as $surveySections)
                                            <tr>
                                                <td>
                                                    <?php echo ($surveySections->section_account_id == "-1") ?"Khách hàng" :"";?>
                                                </td>
                                                <td class="hidden-480">
                                                    <?php echo ($surveySections->section_connected === 0) ? "<span class='label label-warning'>Không liên hệ được KH</span>" : ($surveySections->section_connected === 1 ? "<span class='label label-danger arrowed-in'>KH không đồng ý khảo sát</span>" : "<span class='label label-success arrowed'>KH đồng ý khảo sát</span>") ?>
                                                </td>
                                                <td class="hidden-480">{{$surveySections->section_note}}</td>
                                                <td class="hidden-480">
                                                    <?php echo ($surveySections->section_action === 1) ?'Không làm gì' :($surveySections->section_action === 2 ?'Tạo checklist' :($surveySections->section_action === 3 ?'Tạo checklist INDO' :'Chuyển phòng ban khác'));?>
                                                </td>
                                                <td><strong>{{$surveySections->survey_title}}</strong></td>
                                                <td class="hidden-480">{{$surveySections->name}}</td>
                                                <td class="hidden-480">{{date("d-m-Y H:i:s", strtotime($surveySections->section_time_completed))}}</td>
                                                <td class="hidden-480">
                                                    <?php if($surveySections->section_survey_id > 0) {//chưa gặp người sử dụng  ?>
                                                    <div class="visible-md visible-lg hidden-sm hidden-xs btn-group">
                                                        <a class="open-tooltip" href="#modal-table" role="button" data-toggle="modal" title="Chi Tiết"><span class="badge badge-info">i</span></a>
														<input type="hidden" value="{{$surveySections->section_id}}" name="survey" />
                                                    </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div> 
                <div id="modal-table" class="modal fade" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header no-padding">
                                <div class="table-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                        <span class="white">&times;</span>
                                    </button>
                                    Chi tiết khảo sát
                                </div>
                            </div>

                            <div class="modal-body">
                                
                            </div>

                            <div class="modal-footer no-margin-top">
                                <button class="btn btn-sm btn-danger pull-left" data-dismiss="modal">
                                    <i class="icon-remove"></i>
                                    Close
                                </button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- PAGE CONTENT ENDS -->
            </form>
            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->
    <input type="button" id="btnBack" class="btn-primary" value="Quay về màn hình khảo sát" />
</div><!-- /.page-content -->

<link rel="stylesheet" href="{{asset('assets/css/chosen.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/css/font-awesome.min.css')}}" />

<script src="{{asset('assets/js/jquery-2.0.3.min.js')}}"></script>
<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.bootstrap.js')}}"></script>

<script type="text/javascript">
jQuery(function ($) {
    $('#btnBack').click(function(){
        location.href = '<?php echo url('/'); ?>';
    });
    
    $(".chosen-select").chosen({
        no_results_text: "<?php echo trans($common . ".no_results_text_chosen_box"); ?>"
    });

    var oTable1 = $('#sample-table-2').dataTable({
        "aoColumns": [
            {"bSortable": false}, null, null, null, null, null, null, {"bSortable": false}
        ],
        "bJQueryUI": false,
        "oLanguage": {
            "sLengthMenu": "Hiển thị _MENU_ bản tin mỗi trang",
            "sZeroRecords": "Không tìm thấy",
            "sInfo": "Có _START_ tới _END_ của _TOTAL_ bản ghi",
            "sInfoEmpty": "Có 0 tới 0 của 0 bản ghi",
            "sInfoFiltered": "(Lọc từ _MAX_ tổng số bản ghi)",
            "sSearch": "Tìm kiếm"
        }
    });
    
    
    $( ".open-tooltip" ).click(function(){
        $.ajax({
            url: '<?php echo url('/'.$prefix.'/'.$controller.'/detail_survey') ?>',
            cache: false,
            type: "POST",
            dataType: "html",
            data: {'_token': $('input[name=_token]').val(), 'survey': $(this).parent().find('input[name=survey]').val()},
            success: function (data) {
                $('.modal-body').html(data);
            },
        });
    });
    
});
</script>

@stop