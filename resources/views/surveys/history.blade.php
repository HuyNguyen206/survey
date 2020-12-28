@extends('layouts.app')

@section('content')
    <div class="page-content">
    <?php
    $controller = 'history';
    $transFile = $controller .'.';
    $common = 'common.';
//    $prefix = 'dashboard';
    ?>
    <!-- /.page-header -->
    <!-- PAGE CONTENT BEGINS -->
    <form class="form-horizontal" role="form" method="POST" action="{{ url('/') }}">
        {!! csrf_field() !!}
        <div class="row">
            <div class="col-xs-12" style="overflow: hidden;">
                <div class="space-4"></div>

                <div class="row" style="overflow: hidden;">
                    <div class="row" id='advance_search'>
                        <div class="col-xs-12">
                            <div class="space-4"></div>
                            <div class="row">
                                <div id="div_surveyType" class="col-xs-3" >
                                    <label for="surveyType">{{trans($transFile.'PointOfContact')}}</label>
                                    <select data-placeholder="{{trans($common.'All')}}" name="surveyType[]" id="surveyType" class="search-select chosen-select" multiple>
                                        <option value="1" @if($searchCondition['surveyType'] == 1) selected @endif>{{trans($transFile.'AfterActive')}}</option>
                                        <option value="2" @if($searchCondition['surveyType'] == 2) selected @endif>{{trans($transFile.'AfterChecklist')}}</option>
                                    </select>
                                </div>
                                <div id="div_surveyFrom_surveyTo" class="col-xs-3">
                                    <div class="col-xs-6 no-padding">
                                        <label for="surveyFrom">{{trans($transFile.'FromDate')}}</label>
                                        <div class="inner-addon right-addon">
                                            <i class="glyphicon glyphicon-calendar red"></i>
                                            <input type="text" name="surveyFrom" id="surveyFrom"  value="{{!empty($searchCondition['surveyFrom']) ?date('d-m-Y',strtotime($searchCondition['surveyFrom'])) :date('d-m-Y')}}" class="form-control" style="height: 35px !important">
                                        </div>
                                    </div>
                                    <div class="col-xs-6 no-padding-right" >
                                        <label for="surveyTo">{{trans($transFile.'ToDate')}}</label>
                                        <div class="inner-addon right-addon">
                                            <i class="glyphicon glyphicon-calendar red"></i>
                                            <input type="text" name="surveyTo" id="surveyTo" value="{{!empty($searchCondition['surveyTo']) ?date('d-m-Y',strtotime($searchCondition['surveyTo'])) :date('d-m-Y')}}"  class="form-control" style="height: 35px !important">
                                        </div>
                                    </div>
                                </div>

                                <div id="div_contractNum" class="col-xs-3">
                                    <label for="contractNum">{{trans($transFile.'ContractNumber')}}</label>
                                    <input type="text" name="contractNum" class="form-control" id="contractNum" maxlength="200" value="{{isset($searchCondition['contractNum']) ?$searchCondition['contractNum'] :''}}" style="height: 35px !important">
                                </div>
                                <div id="div_surveyStatus" class="col-xs-3">
                                    <label for="surveyStatus">{{trans($transFile.'ContactResult')}}</label>
                                    <select data-placeholder="{{trans($common.'All')}}" name="sectionConnected[]" id='sectionConnected' class="search-select chosen-select" multiple>
                                        <option value="4" @if(!empty($searchCondition['sectionConnected']) && in_array(4,$searchCondition['sectionConnected'])) selected @endif>{{trans($transFile.'MeetUser')}}</option>
                                        <option value="3" @if(!empty($searchCondition['sectionConnected']) && in_array(3,$searchCondition['sectionConnected'])) selected @endif>{{trans($transFile.'DidntMeetUser')}}</option>
                                        <option value="2" @if(!empty($searchCondition['sectionConnected']) && in_array(2,$searchCondition['sectionConnected'])) selected @endif>{{trans($transFile.'MeetCustomerCustomerDeclinedToTakeSurvey')}}</option>
                                        <option value="1" @if(!empty($searchCondition['sectionConnected']) && in_array(1,$searchCondition['sectionConnected'])) selected @endif>{{trans($transFile.'CannotContact')}}</option>
                                        <option value="0" @if(!empty($searchCondition['sectionConnected']) && in_array(0,$searchCondition['sectionConnected'])) selected @endif>{{trans($transFile.'NoNeedContact')}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top: 10px;">
                                <div class="col-xs-12 center" >
                                    <button class="btn btn-success" id="btnSubmit" type='submit' onclick="clicksubmit()"><i class="icon-search bigger-110"></i>{{trans($common.'Search')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-4"></div>
                    @if(!empty($modelSurveySections) && count($modelSurveySections) > 0)
                    <div class="col-xs-12">
                        <div class="col-xs-6" style='color: #307ecc;font-weight: bold; font-size: 20px; margin: 20px 0;'><div>{{trans($transFile.'Total')}}: {{$modelSurveySections->total()}}</div></div>
                        <div class="col-xs-6"><div class="pull-right">{{$modelSurveySections->links()}}</div></div>
                    </div>
                    @endif
                    <div class="col-xs-12" style="overflow: hidden;">
                        <table id="tableInfoSurvey" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>{{trans($transFile.'Number')}}</th>
                                <th>{{trans($transFile.'ContractNumber')}}</th>
                                <th>{{trans($transFile.'CustomerName')}}</th>
                                <th>{{trans($transFile.'ContactResult')}}</th>
                                <th>{{trans($transFile.'Note')}}</th>
                                <th>{{trans($transFile.'Resolve')}}</th>
                                <th>{{trans($transFile.'PointOfContact')}}</th>
                                <th>{{trans($transFile.'SurveyAgent')}}</th>
                                <th>
                                    <i class="icon-time bigger-110 hidden-480"></i>
                                    {{trans($transFile.'TimeComplete')}}
                                </th>
                                <th>{{trans($transFile.'Edit')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dataPage as $data)
                                <tr>
                                    <td class="hidden-480"></td>
                                    <td class="hidden-480">{{$data['section_contract_num']}}</td>
                                    <td class="hidden-480">{{$data['section_contact_phone']}}</td>
                                    <td class="hidden-480">{{$data['section_connected']}}</td>
                                    <td class="hidden-480">{{$data['section_note']}}</td>
                                    <td class="hidden-480">{{$data['section_action']}}</td>
                                    <td class="hidden-480">{{$data['section_survey_name']}}</td>
                                    <td class="hidden-480">{{$data['section_user_name']}}</td>
                                    <td class="hidden-480">{{$data['section_time_completed']}}</td>
                                    <td class="hidden-480"><a href="{{url('/survey/'.$data['section_contract_num'].'/'.$data['section_survey_id'].'/'.$data['section_code'].'/')}}" target="_blank">{{trans($transFile.'Edit')}}</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                            @if(!empty($modelSurveySections) && count($modelSurveySections) > 0)
                            <tfoot>
                                <tr><td colspan="10">
                                        <span class="pull-left" style='color: #307ecc;font-weight: bold; font-size: 20px; margin: 20px 0;'>{{trans($transFile.'Total')}}: {{$modelSurveySections->total()}}</span>
                                        <span class="pull-right">{{$modelSurveySections->links()}}</span>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- PAGE CONTENT ENDS -->
</div><!-- /.page-content -->

<link type="text/css" href="{{asset('assets/css/datetimepicker.css')}}" rel="stylesheet" media="screen">
<link rel="stylesheet" href="{{asset('assets/css/chosen.css')}}" type="text/css">
<style type="text/css">
    .morecontent span {
        display: none;
    }
    .morelink {
        display: block;
    }
    .height-32{
        height: 32px !important;
    }
    /* enable absolute positioning */
    .inner-addon {
        position: relative;
    }

    /* style glyph */
    .inner-addon .glyphicon {
        position: absolute;
        padding: 10px;
        pointer-events: none;
    }

    /* align glyph */
    .left-addon .glyphicon  { left:  0px;}
    .right-addon .glyphicon { right: 0px;}

    /* add padding  */
    .left-addon input  { padding-left:  30px; }
    .right-addon input { padding-right: 30px; }
</style>
<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.dataTables.bootstrap.js')}}"></script>
<script src="{{asset('assets/js/jquery.shorten.1.0.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/js/bootstrap-datetimepicker.js')}}"></script>
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script src="{{asset('assets/js/fnPagingInfo.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/chosen.jquery.js')}}"></script>
<script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
<script src="{{asset('assets/js/additional-methods.min.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        var oTable1 = $('#tableInfoSurvey').dataTable({
            "aoColumns": [
                <?php
                for ($i = 0; $i < 10; $i++) {
                    if ($i == 0) {
                        echo 'null';
                    } else {
                        echo ',null';
                    }
                }
                ?>
            ],
            //"aaSorting": [[19, "desc"]],
            "bFilter": false,
            "bInfo": false,
            "bSort": false,
            "bPaginate": false,
            "bJQueryUI": false,
            "oLanguage": {
                "sLengthMenu": "{{trans($common.'Show _MENU_ entries')}}",
                "sZeroRecords": "{{trans($common.'No matching records found')}}",
                "sInfo": "{{trans($common.'Showing _START_ to _END_ of _TOTAL_ entries')}}",
                "sInfoEmpty": "{{trans($common.'Showing 0 to 0 of 0 entries')}}",
                "sInfoFiltered": "{{trans($common.'filtered from _MAX_ total entries')}}",
                "sSearch": "{{trans($common.'Search')}}"
            },
            "bServerSide": false,
            "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var page = '<?php echo $currentPage; ?>';
                var length = '<?php echo (Session::has('condition')) ? Session::get('condition')['recordPerPage'] : 15; ?>'; //this.fnPagingInfo().iLength;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', nRow).html(index);
            }
        });

        $('#surveyFrom').datetimepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            minView: 2
        });
        $('#surveyTo').datetimepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            minView: 2
        });
        $('.chosen-select').chosen({
            width: "100%",
            no_results_text: "{{trans($common.'No matching records found')}}",
        });
        ///custom css chosen
        $('.search-field').addClass('height-32');
        $('.chosen-single').css('height', '34px');
        ///

        $('#btnSubmit').click(function () {
            $('#formsubmit').attr('action', '<?php echo url('/') ?>');
            $('#formsubmit').submit();
        });
    });

    function clicksubmit() {
        <?php Session::put('click', 1); ?>
        $('#formsubmit').submit();
    }

</script>
@stop