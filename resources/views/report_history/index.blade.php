@extends('layouts.app')

@section('content')
<div class="page-content">
    <?php
    $controller = 'history';
    $title = 'List Report';
    $transfile = $controller;
    $common = 'common';
    $prefix = 'dashboard';
    $temp = '';

    //Array xử lý
    $arrayAction = [0 => 'Không làm gì',1 => 'Không làm gì', 2 => 'Tạo checklist', 3 => 'PreChecklist', 4 => 'Tạo checklist INDO', 5 => 'Chuyển phòng ban khác'];
    $arrayResult = [0 => "<span class='label label-warning'>Không cần liên hệ</span>", 1 => "<span class='label label-danger arrowed-in'>Không liên lạc được</span>", 2 => "<span class='label label-danger arrowed-in'>Gặp KH, KH từ chối CS</span>", 3 => "<span class='label label-danger arrowed-in'>Không gặp người SD</span>", 4 => "<span class='label label-success arrowed'>Gặp người SD</span>"];
    $classTypeSurvey = [0=>"",1 => "label label-info arrowed arrowed-right", 2 =>  "label label-success arrowed arrowed-right"];
    $emotions = [1 => 'Point_01.png', 2 => 'Point_02.png', 3 => 'Point_03.png', 4 => 'Point_04.png', 5 => 'Point_05.png'];
    $surveyTitle = [1 => 'Sau triển khai', 2 => 'Sau bảo trì'];
    $surveyImprove = ['' => '', -1 => 'Chưa trả lời', 16 => 'Giá cả', 17 => 'Chất lượng dịch vụ', 18 => 'Thái độ nhân viên', 19 => 'Thủ tục', 27 => 'TIN/PNC', 28 => 'Giá cước', 29 => 'Chất lượng thiết bị', 30 => 'Chất lượng đường truyền', 31 => 'Sale', 32 => 'Khác'];
    
//    if(is_string($modelSurveySections)){
//        $modelSurveySections = json_decode($modelSurveySections);
//    }
    ?>
    <!--@include('layouts.pageheader', ['controller' => $controller, 'title' => $title, 'transfile' => $transfile])-->
    <!-- /.page-header -->
    
    <!-- PAGE CONTENT BEGINS -->
    <form id='formsubmit' class="form-horizontal" role="form" method="POST" action="<?php echo url('/' . $prefix . '/' . $controller . '/index') ?>">
        {!! csrf_field() !!}
        <div class="">
            <div class="col-xs-12">
                
                <div class="space-4"></div>
				
                <div class="row">
                        <div class="row" id='advance_search'>
                        <div class="col-xs-12">

                            <div class="space-4"></div>
                            
                            <div class="row">
                                <div class="col-xs-3" >
                                    <label for="departmentType">Bộ phận/ Trung tâm</label>
                                    <select name="departmentType" id="departmentType" class="search-select chosen-select">
                                        <option value="1" @if($searchCondition['departmentType'] == 1) selected @endif>IBB</option>
                                        <option value="2" @if($searchCondition['departmentType'] == 2) selected @endif>TIN</option>
                                        <option value="3" @if($searchCondition['departmentType'] == 3) selected @endif>PNC</option>
                                        <option value="4" @if($searchCondition['departmentType'] == 4) selected @endif>INDO</option>
                                        <option value="5" @if($searchCondition['departmentType'] == 5) selected @endif>BĐH</option>
                                        <option value="6" @if($searchCondition['departmentType'] == 6) selected @endif>CS</option>
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="surveyType">Loại khảo sát</label>
                                    <select data-placeholder="Tất cả" name="surveyType" id="surveyType" class="search-select chosen-select">
                                        <option value="1" @if($searchCondition['type'] == 1) selected @endif>Triển khai</option>
                                        <option value="2" @if($searchCondition['type'] == 2) selected @endif>Bảo trì</option>
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="region">Vùng</label>
                                    <select data-placeholder="Toàn quốc" name="region[]" id='region_sel' class="search-select chosen-select" multiple>
                                        @for($i = 1; $i <= 7; $i++)
                                        @if(!empty($searchCondition['region']) && in_array($i, $searchCondition['region']))
                                            <option selected="selected" value="{{$i}}">Vùng {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Vùng {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="location">Chi nhánh</label>
                                    <select data-placeholder="Toàn quốc" name="location[]" id='location_sel' class="search-select chosen-select" multiple>
                                        @if(!empty($modelLocation))
                                        @foreach($modelLocation as $location)
                                            <?php 
                                                $t = substr($location->region, -1);
                                                $val = $location->id;
                                                $name = $location->name;
                                                if(!empty($location->branchcode)){
                                                    $val = $location->id.'_'.$location->branchcode;
                                                    $name = str_replace(' - ', $location->branchcode.'-', $location->name);
                                                }
                                            ?>
                                            @if($location->region != $temp)
                                            <optgroup class="region{{$t}}" label="{{$location->region}}" >
                                            @if(in_array($location->id, [4,8]))
                                            @if(!empty($searchCondition['location']) && in_array($location->id, $searchCondition['location']))
                                            <option selected="selected" value="{{$location->id}}">{{$location->name}}</option>
                                            @else
                                            <option value="{{$location->id}}">{{$location->name}}</option>
                                            @endif
                                            @endif
                                            @endif
                                            @if(!empty($searchCondition['location']) && in_array($val, $searchCondition['location']))
                                            <option selected="selected" value="{{$val}}">{{$name}}</option>
                                            @else
                                            <option value="{{$val}}">{{$name}}</option>
                                            @endif
                                            <?php $temp = $location->region;?>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="space-4"></div>
                            
                            <div class="row">
                                <div id='div_sale' class="col-xs-3" >
                                    <label for="CSATPointSale">CSAT NV Kinh Doanh</label>
                                    <select data-placeholder="Tất cả" name="CSATPointSale[]" id="CSATPointSale" class="search-select chosen-select" multiple>
                                        @for($i = 1; $i <= 5; $i++)
                                        @if(!empty($searchCondition['CSATPointSale']) && in_array($i, $searchCondition['CSATPointSale']))
                                            <option selected="selected" value="{{$i}}">Điểm {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Điểm {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                                <div id="div_maintain" class="col-xs-3" hidden="" >
                                    <label for="CSATPointBT">CSAT NV Bảo trì</label>
                                    <select data-placeholder="Tất cả" name="CSATPointBT[]" id="CSATPointBT" class="search-select chosen-select" multiple>
                                        @for($i = 1; $i <= 5; $i++)
                                        @if(!empty($searchCondition['CSATPointBT']) && in_array($i, $searchCondition['CSATPointBT']))
                                            <option selected="selected" value="{{$i}}">Điểm {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Điểm {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                                <div id="div_deploy" class="col-xs-3" >
                                    <label for="CSATPointNVTK">CSAT NV Triển khai</label>
                                    <select data-placeholder="Tất cả" name="CSATPointNVTK[]" id="CSATPointNVTK" class="search-select chosen-select" multiple>
                                        @for($i = 1; $i <= 5; $i++)
                                        @if(!empty($searchCondition['CSATPointNVTK']) && in_array($i, $searchCondition['CSATPointNVTK']))
                                            <option selected="selected" value="{{$i}}">Điểm {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Điểm {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="CSATPointNet">CSAT Dịch vụ Internet</label>
                                    <select data-placeholder="Tất cả" name="CSATPointNet[]" class="search-select chosen-select" multiple>
                                        @for($i = 1; $i <= 5; $i++)
                                        @if(!empty($searchCondition['CSATPointNet']) && in_array($i, $searchCondition['CSATPointNet']))
                                            <option selected="selected" value="{{$i}}">Điểm {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Điểm {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="CSATPointTV">CSAT Dịch vụ TV</label>
                                    <select data-placeholder="Tất cả" name="CSATPointTV[]" class="search-select chosen-select" multiple>
                                        @for($i = 1; $i <= 5; $i++)
                                        @if(!empty($searchCondition['CSATPointTV']) && in_array($i, $searchCondition['CSATPointTV']))
                                            <option selected="selected" value="{{$i}}">Điểm {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Điểm {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-4"></div>
                            
                            <div class="row">
                                <div class="col-xs-3" >
                                    <label for="NPSPoint">Điểm NPS</label>
                                    <select data-placeholder="Tất cả" name="NPSPoint[]" class="search-select chosen-select" multiple>
                                        @for($i = 0; $i <= 10; $i++)
                                        @if(!empty($searchCondition['NPSPoint']) && in_array($i, $searchCondition['NPSPoint']))
                                            <option selected="selected" value="{{$i}}">Điểm {{$i}}</option>
                                        @else 
                                            <option value="{{$i}}">Điểm {{$i}}</option>
                                        @endif
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="RateNPS">Công ty cần làm gì để NPS 10</label>
                                    <select data-placeholder="Tất cả" name="RateNPS[]" class="search-select chosen-select" multiple>
                                        <option value="27" @if(!empty($searchCondition['RateNPS']) && in_array(27,$searchCondition['RateNPS'])) selected @endif>TIN/PNC</option>
                                        <option value="28" @if(!empty($searchCondition['RateNPS']) && in_array(28,$searchCondition['RateNPS'])) selected @endif>Giá cước</option>
                                        <option value="29" @if(!empty($searchCondition['RateNPS']) && in_array(29,$searchCondition['RateNPS'])) selected @endif>Chất lượng thiết bị</option>
                                        <option value="30" @if(!empty($searchCondition['RateNPS']) && in_array(30,$searchCondition['RateNPS'])) selected @endif>Chất lượng đường truyền</option>
                                        <option value="31" @if(!empty($searchCondition['RateNPS']) && in_array(31,$searchCondition['RateNPS'])) selected @endif>Sale</option>
                                        <option value="32" @if(!empty($searchCondition['RateNPS']) && in_array(32,$searchCondition['RateNPS'])) selected @endif>Khác</option>
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="processingSurvey">Xử lý</label>
                                    <select data-placeholder="Tất cả" name="processingSurvey[]" class="search-select chosen-select" multiple>
                                        <option value="1" @if(!empty($searchCondition['section_action']) && in_array(1,$searchCondition['section_action'])) selected @endif>Không làm gì</option>
                                        <option value="2" @if(!empty($searchCondition['section_action']) && in_array(2,$searchCondition['section_action']))) selected @endif>Tạo Checklist</option>
                                        <option value="3" @if(!empty($searchCondition['section_action']) && in_array(3,$searchCondition['section_action'])) selected @endif>PreChecklist</option>
                                        <option value="4" @if(!empty($searchCondition['section_action']) && in_array(4,$searchCondition['section_action'])) selected @endif>Tạo Checklist INDO</option>
                                        <option value="5" @if(!empty($searchCondition['section_action']) && in_array(5,$searchCondition['section_action'])) selected @endif>Chuyển phòng ban khác</option>
                                    </select>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="surveyStatus">Tình trạng</label>
                                    <select data-placeholder="Tất cả" name="surveyStatus[]" class="search-select chosen-select" multiple>
                                        <option value="4" @if(!empty($searchCondition['section_connected']) && in_array(4,$searchCondition['section_connected'])) selected @endif>Gặp người SD</option>
                                        <option value="3" @if(!empty($searchCondition['section_connected']) && in_array(3,$searchCondition['section_connected'])) selected @endif>Không gặp người SD</option>
                                        <option value="2" @if(!empty($searchCondition['section_connected']) && in_array(2,$searchCondition['section_connected'])) selected @endif>Gặp KH, KH từ chối CS</option>
                                        <option value="1" @if(!empty($searchCondition['section_connected']) && in_array(1,$searchCondition['section_connected'])) selected @endif>Không liên lạc được</option>
                                        <option value="0" @if(!empty($searchCondition['section_connected']) && in_array(0,$searchCondition['section_connected'])) selected @endif>Không cần liên hệ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-4"></div>
                            
                            <div class="row">
                                <div class="col-xs-3" >
                                    <div class="col-xs-6 no-padding" >
                                        <label for="survey_from">Từ ngày khảo sát</label>
                                        <div class="inner-addon right-addon">
                                            <i class="glyphicon glyphicon-calendar red"></i>
                                            <input type="text" name="survey_from" id="surveyFrom"  value="{{!empty($searchCondition['survey_from']) ?date('d-m-Y',strtotime($searchCondition['survey_from'])) :date('d-m-Y')}}" class="form-control"> 
                                        </div>
                                    </div>
                                    <div class="col-xs-6 no-padding-right" >
                                        <label for="survey_to">Đến ngày khảo sát</label>
                                        <div class="inner-addon right-addon">
                                            <i class="glyphicon glyphicon-calendar red"></i>
                                            <input type="text" name="survey_to" value="{{!empty($searchCondition['survey_to']) ?date('d-m-Y',strtotime($searchCondition['survey_to'])) :date('d-m-Y')}}" id="surveyTo" class="form-control"> 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-3" >
                                    <label for="contractNum">Số HĐ</label>
                                    <input type="text" name="contractNum" class="form-control" 
                                        id="contractNum" maxlength="200" value="{{isset($searchCondition['contractNum']) ?$searchCondition['contractNum'] :''}}">
                                </div>
                                <div class="col-xs-3" >
                                    <label for="contractNum">{{trans($transfile.'.User Survey')}}</label>
                                    <input type="text" name="user_survey" class="form-control" 
                                        id="contractNum" maxlength="200" value="{{isset($searchCondition['userSurvey']) ?$searchCondition['userSurvey'] :''}}">
                                </div>
                                <div class="col-xs-3" >
                                    <label for="salerName">{{trans($transfile.'.NVKD')}}</label>
                                    <input type="text" name="salerName" class="form-control" 
                                        id="contractNum" maxlength="200" value="{{isset($searchCondition['salerName']) ?$searchCondition['salerName'] :''}}">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-xs-3" style="padding-top: 10px" >
                                    <label for="technicalStaff">{{trans($transfile.'.Technical Staff')}}</label>
                                    <input type="text" name="technicalStaff" class="form-control" 
                                        id="contractNum" maxlength="200" value="{{isset($searchCondition['technicalStaff']) ?$searchCondition['technicalStaff'] :''}}">
                                </div>
                            </div>

                            <div class="space-4"></div>
                            <div class="row">
                                <div class="col-xs-12 center" >
                                    <button class="btn btn-success" type='submit' onclick="clicksubmit()"><i class="icon-search bigger-110"></i>Tìm</button>
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div class="space-4"></div>
                    <?php if(!empty($modelSurveySections) && count($modelSurveySections) > 0) {?>
                    <div class="row">
                        <div class="col-xs-6" style='color: #307ecc;font-weight: bold; font-size: 20px; margin: 20px 0;'><div>Tổng số dòng: {{$modelSurveySections->total()}}</div></div>
                        <div class="col-xs-6"><div class="pull-right">{{$modelSurveySections->links()}}</div></div>
                    </div>
                    <?php } ?>
                    <div class="table-responsive" style="overflow: scroll">
                        <table id="tableInfoSurvey" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,5]) === false) hidden @endif>{{trans($transfile.'.NVKD')}}</th>
                                    <th @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,4,5]) === false) hidden @endif>{{trans($transfile.'.NVTK')}}</th>
                                    <th @if($searchCondition['type'] != 2 || array_search($searchCondition['departmentType'],[2,3,4,5]) === false) hidden @endif>{{trans($transfile.'.NVBT')}}</th>
                                    <th @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,5]) === false) hidden @endif>{{trans($transfile.'.CSAT NVKD')}}</th>
                                    <th @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,4,5]) === false) hidden @endif>{{trans($transfile.'.CSAT TK')}}</th>
                                    <th @if($searchCondition['type'] != 2 || array_search($searchCondition['departmentType'],[2,3,4,5]) === false) hidden @endif>{{trans($transfile.'.CSAT BT')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.CSAT TH')}}</th>
									<th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.CSAT NET')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.NPS Point')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Improve Service')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Section Survey')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Section Action')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Section Status')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Section Contract number')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Section Phone')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.User Survey')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Region')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Branches')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{trans($transfile.'.Note')}}</th>
                                    <th @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>
                                        <i class="icon-time bigger-110 hidden-480"></i>
                                        {{trans($common.'.Create at')}}
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if(!empty($modelSurveySections)){ ?>
                                @foreach($modelSurveySections as $surveySections)
                                <?php
                                    if(strpos($surveySections->caithien_nps, ',') !== false){ 
                                        $tempImprove = explode(',', $surveySections->caithien_nps);
                                        $surveySections->caithien_nps = '';
                                        foreach($tempImprove as $val){
                                            $surveySections->caithien_nps .= $surveyImprove[$val].',';
                                        }
                                        $surveySections->caithien_nps = substr($surveySections->caithien_nps, 0,-1);
                                    }  else {
                                        $surveySections->caithien_nps = $surveyImprove[$surveySections->caithien_nps];
                                    }
                                ?>
                                <tr>
                                    <td class="hidden-480"></td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,5]) === false) hidden @endif >{{(!empty($surveySections->salename)) ?$surveySections->salename :''}}</td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,4,5]) === false) hidden @endif> {{($surveySections->section_survey_id == 1 && !empty($surveySections->section_supporter)) ?$surveySections->section_supporter :''}} {{($surveySections->section_survey_id == 1 && !empty($surveySections->section_subsupporter)) ?$surveySections->section_subsupporter :''}}</td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 2 || array_search($searchCondition['departmentType'],[2,3,4,5]) === false) hidden @endif> {{($surveySections->section_survey_id == 2 && !empty($surveySections->section_supporter)) ?$surveySections->section_supporter :''}} {{($surveySections->section_survey_id == 2 && !empty($surveySections->section_subsupporter)) ?$surveySections->section_subsupporter :''}}</td>
                                    <td class="hidden-480 center" @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,5]) === false) hidden @endif >@if(!empty($surveySections->csat_kinhdoanh))<span><strong><img src="{{asset("assets/img/".$emotions[$surveySections->csat_kinhdoanh])}}" style="width: 25px;height: 25px" /></strong></span>@endif<br/> {{!empty($surveySections->csat_kinhdoanh) ?$surveySections->csat_kinhdoanh :''}}</td>
                                    <td class="hidden-480 center" @if($searchCondition['type'] != 1 || array_search($searchCondition['departmentType'],[1,2,3,4,5]) === false) hidden @endif>@if(!empty($surveySections->csatkythuat))<span><strong><img src="{{asset("assets/img/".$emotions[$surveySections->csatkythuat])}}" style="width: 25px;height: 25px" /></strong></span>@endif<br/> {{!empty($surveySections->csatkythuat) ?$surveySections->csatkythuat :''}}</td>
                                    <td class="hidden-480 center" @if($searchCondition['type'] != 2 || array_search($searchCondition['departmentType'],[2,3,4,5]) === false) hidden @endif>@if(!empty($surveySections->csatkythuat))<span><strong><img src="{{asset("assets/img/".$emotions[$surveySections->csatkythuat])}}" style="width: 25px;height: 25px" /></strong></span>@endif<br/> {{!empty($surveySections->csatkythuat) ?$surveySections->csatkythuat :''}}</td>
                                    <td class="hidden-480 center" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>@if(!empty($surveySections->csattruyenhinh))<span><strong><img src="{{asset("assets/img/".$emotions[$surveySections->csattruyenhinh])}}" style="width: 25px;height: 25px" />@endif</strong></span> <br/> {{!empty($surveySections->csattruyenhinh) ?$surveySections->csattruyenhinh :''}} </td>
									<td class="hidden-480 center" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>@if(!empty($surveySections->csatinternet))<span><strong><img src="{{asset("assets/img/".$emotions[$surveySections->csatinternet])}}" style="width: 25px;height: 25px" />@endif</strong></span> <br/> {{!empty($surveySections->csatinternet) ?$surveySections->csatinternet :''}} </td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{$surveySections->nps}}</td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{$surveySections->caithien_nps}}</td>
                                    <td @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif><span class="{{$classTypeSurvey[$surveySections->section_survey_id]}}"><strong>{{$surveyTitle[$surveySections->section_survey_id]}}</strong></span></td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>
                                        <?php
                                        echo $arrayAction[$surveySections->section_action];
                                        ?>
                                    </td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>
                                        <?php
                                        echo $arrayResult[$surveySections->section_connected];
                                        ?>
                                    </td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>
                                        <?php
                                        echo $surveySections->section_contract_num;
                                        ?>
                                    </td>
                                    <td class="hidden-480">
                                        {{$surveySections->section_contact_phone}}
                                    </td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>
                                        {{$surveySections->section_user_name}}
                                    </td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{str_replace('Vung', 'Vùng', $surveySections->section_sub_parent_desc)}}</td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{$surveySections->section_location}}</td>
                                    <td class="hidden-480 more width-20" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{$surveySections->section_note}}</td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>{{date("d-m-Y H:i:s", strtotime($surveySections->section_time_start))}}</td>
                                    <td class="hidden-480" @if($searchCondition['type'] != 1 && $searchCondition['departmentType'] == 1) hidden @endif>
                                        <?php if ($surveySections->section_survey_id > 0) {//chưa gặp người sử dụng   ?>
                                            <span class="visible-md visible-lg hidden-sm hidden-xs btn-group">
                                                <a class="open-tooltip" href="#modal-table" onclick="open_tooltip({{$surveySections->section_id}},'{{$surveySections->section_contract_num}}','{{$surveySections->section_contact_phone}}')" role="button" data-toggle="modal" title="Chi Tiết"><span class="badge badge-info">i</span></a>
                                            </span>
                                        <?php } ?>
										<?php if ($surveySections->section_connected == 4 && !empty($surveySections->section_contact_phone)) { ?>
											<span class="visible-md visible-lg hidden-sm hidden-xs btn-group">
												<a style="cursor: pointer; text-decoration: none;" onclick="checkVoiceRecord({{$surveySections->section_id}})"><span class="icon-headphones bigger-110"></span></a>
											</span>
										<?php } ?>
                                    </td>
                                </tr>
                                @endforeach
                                <?php } ?>
                            </tbody>
                            <?php if(!empty($modelSurveySections) && count($modelSurveySections) > 0) {?>
                            <tfoot>
                                <tr><td colspan="19">
                                        <span class="pull-left" style='color: #307ecc;font-weight: bold; font-size: 20px; margin: 20px 0;'>Tổng số dòng: {{$modelSurveySections->total()}}</span>
                                        <span class="pull-right">{{$modelSurveySections->links()}}</span>
                                    </td>
                                </tr>
                            </tfoot>
                            <?php } ?>
                        </table>
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
</div><!-- /.page-content -->


<div id="modal-table-record" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header no-padding">
				<div class="table-header">
					<button type="button" class="close" data-dismiss="modal" onclick="stopAll()" aria-hidden="true">
						<span class="white">&times;</span>
					</button>
					Chi tiết các cuộc gọi
				</div>
			</div>

			<div class="modal-body" id="modal-table-record-body">
			</div>

			<div class="modal-footer no-margin-top">
				<button class="btn btn-sm btn-danger pull-left" data-dismiss="modal" onclick="stopAll()">
					<i class="icon-remove"></i>
					Close
				</button>
			</div>
		</div>
	</div>
</div>

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
<script type="text/javascript">
$(document).ready(function() {
    init();
    var oTable1 = $('#tableInfoSurvey').dataTable({
        "aoColumns": [
            null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, {"sType": "date", "bSortable": true}, {"bSortable": false}
        ],
        //"aaSorting": [[19, "desc"]],
        "bInfo": false,
        "bSort": false,
        "bPaginate": false,
        "bJQueryUI": false,
        "oLanguage": {
            "sLengthMenu": "Hiển thị _MENU_ dòng mỗi trang",
            "sZeroRecords": "Không tìm thấy",
            "sInfo": "Có _START_ tới _END_ của _TOTAL_ bản ghi",
            "sInfoEmpty": "Có 0 tới 0 của 0 bản ghi",
            "sInfoFiltered": "(lọc từ _MAX_ tổng số bản ghi)",
            "sSearch": "Tìm kiếm"
        },
        "bServerSide": false,
        "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
            var page = <?php echo $currentPage; ?>;
            var length = <?php echo (Session::has('condition')) ?Session::get('condition')['recordPerPage']  :15;?>;//this.fnPagingInfo().iLength;
            var index = (page * length + (iDisplayIndex +1));
            $('td:eq(0)', nRow).html(index);
        }
    });
    
    //
    $('.more').shorten({
        moreText: 'Xem thêm <i class=\"icon-plus\"></i>',
        lessText: 'Rút gọn <i class=\"icon-minus\"></i>',
        showChars: 300
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
        no_results_text: "Không tìm thấy",
    });
    ///custom css chosen
    $('.search-field').addClass('height-32');
    $('.chosen-single').css('height','34px');
    ///
    $('#region_sel').change(function(){
        var id = $(this).val();
        if(id != null) {
            $('#location_sel optgroup').attr('disabled',true);
            $.each(id, function(key, val) {
                $('#location_sel .region'+val).removeAttr('disabled');
                $('#location_sel').find('optgroup:first').hide();
            });
        }else{
            $('#location_sel optgroup').removeAttr('disabled');
        }
        $('#location_sel').trigger('chosen:updated');
    })
    ///
    $('#departmentType').change(function(){
        var a = $(this).val();
        if(a != 1){//IBB
            $('#surveyType option[value=2]').show();
        }else{
            $('#surveyType option[value=2]').hide();
        }
        $('#surveyType').trigger('chosen:updated');
    });
    ///
    $('#surveyType').change(function(){
        showHideInput($(this).val());
    });
});
function clicksubmit(){
    <?php Session::put('click',1); ?>
    $('#formsubmit').submit();
}
function open_tooltip(id, contract, phone){
    $.ajax({
            url: '<?php echo url('/' . $prefix . '/' . $controller . '/detail_survey') ?>',
            cache: false,
            type: "POST",
            dataType: "html",
            data: {'_token': $('input[name=_token]').val(), 'survey': id, 'contract': contract, 'phone': phone},
            success: function (data) {
                $('.modal-body').html(data);
            },
    });
}

function checkVoiceRecord(id){
	var a = '<div class="center" id="spinner"><img src="{{asset("assets/img/bluespinner.gif")}}" /></div>';
	$('#modal-table-record-body').html(a);
	$('#modal-table-record').modal().show();
	$.ajax({
		url: '<?php echo url('get-voice-records-ajax') ?>',
		cache: false,
		type: "POST",
		dataType: "JSON",
		data: {'_token': $('input[name=_token]').val(), 'id': id},
		success: function (data) {
			if(data.state === 'fail'){
				$('#modal-table-record-body').html(data.error);
				return;
			}
			
			var a = data.detail;
			$('#modal-table-record-body').html(a);
		},
		error: function(){
			$('#modal-table-record-body').html('Lỗi hệ thống');
		}
    });
}

function stopAll(){
	var a = '';
	$('#modal-table-record-body').html(a);
}

function init(){
    <?php if(!empty($searchCondition['departmentType']) && $searchCondition['departmentType'] != 1) {?>
        $('#surveyType option[value=2]').show();
    <?php } else { ?>
        $('#surveyType option[value=2]').hide();
    <?php } 
        if(!empty($searchCondition['type'])){?>
        showHideInput({{$searchCondition['type']}});
    <?php } ?>
}
function showHideInput(typeSurvey){
    if(typeSurvey == 2){
        $('#div_maintain').show(500);
        $('#div_deploy').hide(500);
        $('#div_sale').hide(500);
        //set lại giá trị mặc định null
        $('#CSATPointSale').val('').trigger('chosen:updated');
        $('#CSATPointNVTK').val('').trigger('chosen:updated');
    } else {
        $('#div_maintain').hide(500);
        $('#div_deploy').show(500);
        $('#div_sale').show(500);
        //set lại giá trị mặc định null
        $('#CSATPointBT').val('').trigger('chosen:updated');
    }
}
</script>
<style>
    .answer{
            word-wrap: break-word !important;
    }
</style>
@stop