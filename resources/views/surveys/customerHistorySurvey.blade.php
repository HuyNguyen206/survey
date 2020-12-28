<?php $transFile = 'customerHistoryInfo'; ?>
<!-- Lịch sử hỗ trợ -->
<span style="position: fixed; text-align: center; z-index: 99999" class='col-md-12'us-spinner="{radius:30, width:8, length: 16}"></span>
<div class="col-xs-12 fix-pad form-info-customer display-block bg-gray ">
    <div class="col-xs-12 div-title bg-6b79c4">{{trans($transFile.'.assistHistory')}}</div>
    <div class="col-xs-12 fix-pad" style="max-height: 400px; overflow-y: auto;">
        <?php $i=1; ?>
        @foreach ($responseInfo['data_history'] as $key => $value)
        <table class="tb-row-white" style="width: 100%" >
            <tr>
                <td rowspan="2" class="number-light">{{$i++}}</td>
                <td class="border-gray">{{trans($transFile.'.accountAgentCall')}}: <strong>{{$value->HelpdeskName}}</strong>, {{trans($transFile.'.customer')}}: <strong>{{$value->ContactName}}</strong>,
                    {{trans($transFile.'.start')}}: <strong>{{$value->StartDate}}</strong>, {{trans($transFile.'.end')}}:<strong>{{$value->EndDate}}</strong>,</td>
            </tr>
            <tr class="border-gray">
                <td><span style="color:#f2546d">{{trans($transFile.'.information')}}:</span>   {{$value->SupportInfo}}</td>
            </tr>
        </table>
        @endforeach
    </div>
</div>

<div class="col-xs-12 fix-pad form-info-customer display-block bg-gray ">
    <div class="col-xs-12 div-title bg-f2c354"><span>{{trans($transFile.'.surveyHistory')}}</span></div>
    <div class="col-xs-12 fix-pad" style=" max-height: 400px; overflow-y: auto;">
        <?php $i = 1; ?>
        @foreach ($responseInfo['outbound_history'] as $key => $value)
        <table class="tb-row-white" style="width: 100%">
            <tr>
                <td rowspan="2" class="number-light" >{{$i++}}</td>
                <td class="border-gray">{{trans($transFile.'.surveyAgent')}}:<strong>{{$value['section_user_name']}}</strong>,
                    {{trans($transFile.'.start')}}: <strong>{{$value['section_time_start']}}</strong>,
                    {{trans($transFile.'.end')}}:<strong> {{$value['section_time_completed']}}</strong>,
                    {{trans($transFile.'.surveyResult')}}: <strong> <span>{{trans($transFile.'.'.$arrayConnected[$value['section_connected']])}}</span></strong>,
                    {{trans($transFile.'.processingMethod')}}: <strong> <span>{{trans($transFile.'.'.$arrayAction[$value['section_action']]['actionKey'])}}</span></strong>,
                    {{trans($transFile.'.surveyContent')}}: <strong>{{trans($transFile.'.'.$arrayPointOfContact[$value['section_survey_id']])}}</strong>,
                </td>
            </tr>
        </table>
        @endforeach
    </div>
</div>
<!-- end lịch sử hỗ trợ -->