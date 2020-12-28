<?php $transFile = 'surveyDashboard'; ?>
<form method="POST" action="{{ url('createSurvey/')}}" id="surveyHiFPT">
    {!! csrf_field() !!}

<!--    <input type="hidden" value="{{$accountInfo['ObjID']}}" name="objID">
    <input type="hidden" value="{{$accountInfo['LocationID']}}" name="LocationID">
    <input type="hidden" value="{{$paramSurveySection['num_type']}}" name="typeSurvey">
    <input type="hidden" value="{{$paramSurveySection['shd']}}" name="contractNum">
    <input type="hidden" value="{{$paramSurveySection['code']}}" name="codedm">-->
    <input type="hidden" value="12" name="objID">
    <input type="hidden" value="1000" name="LocationID">
    <input type="hidden" value="1" name="typeSurvey">
    <input type="hidden" value="PPD000099" name="contractNum">
    <input type="hidden" value="1056950142" name="codedm">

    <div class="col-xs-12 fix-pad form-info-customer display-block bg-gray ">
        <div class="col-xs-12 div-title bg-36beb3">{{trans($transFile.'.surveyDashboard')}}</div>
        <div class="col-xs-12 fix-pad">
            <div class="col-xs-12 fix-pad" >
                <div class="radio-survey">
                    <?php
                    $connected = (old('connected') !== null) ? old('connected') : 0;
                    ?>
                    <ul class="ul-radio-rate">
                        <li><p style="padding-top: 10px;padding-left: 15px;color: #f2546d;"> {{trans($transFile.'.contactResult')}}</p></li>
                        @foreach($arrayConnected as $connectedID => $connectedKey)
                        <li>
                            <label> <input type="radio" name="connected" idConnection="{{$connectedID}}" {{($connected == $connectedID) ? 'checked="checked"' : ''}} class="show-box3 survey-question connection"  value="{{$connectedID}}"><span class="verticle-radio">{{trans('connected'.'.'.$connectedKey)}}</span></label>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-xs-12 fix-pad">
                <div name="radio-survey-1" class="radio-survey type-survey" style="margin: 0px;">
                    <?php
                    $type = (old('type') !== null) ? old('type') : $paramSurveySection['num_type'];
                    ?>
                    <ul class="ul-radio-rate">
                        <li><p style="padding-top: 10px;padding-left: 15px;color: #f2546d;padding-right: 40px">{{trans($transFile.'.surveyType')}}</p></li>
                        @foreach($arrayPointOfContact as $pointOfContactID => $pointOfContactKey)
                        <li>
                            <label> <input type="radio" name="type" class="md-primary group-question after-deploy survey-question" value="{{$pointOfContactID}}" {{($type == $pointOfContactID) ? 'checked="checked"' : 'disabled'}}><span class="verticle-radio">{{trans('pointOfContact'.'.'.$pointOfContactKey)}}</span></label>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-xs-12 padding-10 border-gray">
                <?php
                $name = (old('contactName') !== null) ? old('contactName') : null;
                $phone = (old('contactPhone') !== null) ? old('contactPhone') : null;
                $note = (old('note') !== null) ? old('note') : null;
                ?>
                <div class="col-xs-7 fix-pad">
                    <p class="color-pink">{{trans($transFile.'.contactPerson')}}</p>
                    <div class="col-xs-6 fix-pad">
                        <input type="text" class="form-control height-50 pull-left" style="font-size: 14px"  name="contactName" placeholder="{{trans($transFile.'.contactPersonName')}}" value="{{$name}}">
                    </div>
                    <div class="col-xs-6">
                        <input type="text" class="form-control  height-50 pull-left" style="font-size: 14px" name="contactPhone" placeholder="{{trans($transFile.'.contactPersonPhone')}}" value="{{$phone}}">
                    </div>
                </div>
                <div class="col-xs-5 fix-pad">
                    <p class="color-pink">{{trans($transFile.'.note')}}</p>
                    <textarea name="note" class="form-control height-50" style="background-color: white;">{{$note}}</textarea>
                </div>

                @if ($errors->any())
                @if($errors->has('contact'))
                <div class="col-xs-12" style="padding-top: 15px;">
                    <div class="alert-danger">
                        <ul>
                            {{$errors->first('contact')}}
                        </ul>
                    </div>
                </div>
                @endif
                @endif
            </div>

            <div class="col-xs-12 fix-pad border-gray">
                <?php
                $action = (old('action') !== null) ? old('action') : 1;
                ?>
                <ul class="ul-radio-rate">
                    <li><p style="padding-top: 10px;padding-left: 15px;color: #f2546d;padding-right: 64px;"> {{trans($transFile.'.resolve')}}</p></li>
                    @foreach($arrayAction as $actionID => $actionDetail)
                    <li>
                        <label><input id="{{$actionDetail['actionInputId']}}" type="radio" name="action" {{($action == $actionID) ? 'checked="checked"' : ''}} value="{{$actionID}}" class="survey-question"><span class="verticle-radio">{{trans('action'.'.'.$actionDetail['actionKey'])}}</span></label>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <?php
    $i = 1;
    ?>
    <div id="questionBoard" class="col-xs-12 border-gray" style="@if($connected != 4){{"display:none;"}}@endif padding-top: 10px">
        @foreach($baseQA as $questionID => $base)
        <?php
        $customerAnswerID = !empty(old('rateScore' . $questionID)) ? old('rateScore' . $questionID) : ((isset($customerQA[$questionID])) ? $customerQA[$questionID]['survey_result_answer_id'] : null);
        $customerExtraAnswerID = !empty(old('extraAnswer' . $questionID)) ? old('extraAnswer' . $questionID) : ((isset($customerQA[$questionID])) ? $customerQA[$questionID]['survey_result_answer_extra_id'] : null);
        $customerAction = !empty(old('extraAction' . $questionID)) ? old('extraAction' . $questionID) : ((isset($customerQA[$questionID])) ? $customerQA[$questionID]['survey_result_action'] : null);
        $customerError = !empty(old('extraError' . $questionID)) ? old('extraError' . $questionID) : ((isset($customerQA[$questionID])) ? $customerQA[$questionID]['survey_result_error'] : null);
        $customerNote = !empty(old('subNote' . $questionID)) ? old('subNote' . $questionID) : ((isset($customerQA[$questionID])) ? $customerQA[$questionID]['survey_result_note'] : null);
        ?>
        <div id="question{{$questionID}}" class="panel panel-default panel-default-me">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="fa fa-question-circle"></span> <span class="question-title">{{trans('common.question')}} {{$i++}}</span>: {{trans('question'.'.'.$base['questionKey'])}}?<span class="readmore-link"></span>
                </h3>
            </div>
            <div class="panel-body two-col">
                <div class="radio-survey col-xs-12" style="padding-bottom: 15px;">
                    @foreach($base['answers'] as $answerID => $answer)
                    <div class="col-xs-2">
                        <label style="cursor: pointer"><input idQuestion="{{$questionID}}" type="checkbox" name="rateScore{{$questionID}}" class="survey-question question" {{($customerAnswerID == $answerID)? 'checked="checked"' : "" }} value="{{$answerID}}"/><span class="verticle-radio"><img class="emo-resize" style="width: 20px;" src="{{asset("outboundapp/img/Point_0".$answer['answerPoint'].".png")}}">{{trans('answer'.'.'.$answer['answerKey'])}}({{$answer['answerPoint']}})</span></label>
                    </div>
                    @endforeach
                </div>

                <div class="col-xs-12" style="padding-top: 15px;">
                    <div class="col-xs-3">
                        <div class="col-xs-3" style="padding: 0px"><span class="question-title">{{trans($transFile.'.note')}}</span></div>
                        <div class="col-xs-9" style="padding: 0px">
                            <textarea  name="subNote{{$questionID}}" style="width: 100%" class="note-survey">{{$customerNote}}</textarea>
                        </div>
                    </div>

                    <div class="col-xs-3">
                        <div class="col-xs-3 question-title" style="padding: 0px">{{trans($transFile.'.noComment')}}</div>
                        <select class="col-xs-9" name="extraAnswer{{$questionID}}" {{(!empty($customerAnswerID) && $customerAnswerID != -1) ? 'disabled':""}} style="{{(!empty($customerAnswerID) && $customerAnswerID != -1) ? "background-color: gainsboro":""}}">
                            <option disabled selected>--{{trans('common.select')}}--</option>
                            @foreach($baseAllAns[$mapAliasWithGroupAnswer[$base['questionAlias']]] as $answerID => $answer)
                            <option {{($customerExtraAnswerID == $answerID)?'selected':""}} value="{{$answerID}}">{{trans('answer'.'.'.$answer['answerKey'])}}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(in_array($base['questionAlias'],[5,6]))
                    <div class="col-xs-3">
                        <div class="col-xs-2 question-title" style="padding: 0px">{{trans($transFile.'.resolve')}}</div>
                        <select class="col-xs-10" name="extraAction{{$questionID}}" {{(empty($customerAction)) ? 'disabled':""}} style="{{(empty($customerAction)) ? "background-color: gainsboro":""}}">
                            <option disabled selected>--{{trans('common.select')}}--</option>
                            @foreach($baseAllAns[21] as $answerID => $answer)
                            <option {{($customerAction == $answerID)?'selected':""}} value="{{$answerID}}">{{trans('action'.'.'.$answer['answerKey'])}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xs-3">
                        <div class="col-xs-2 question-title" style="padding: 0px">{{trans($transFile.'.errorType')}}</div>
                        <select class="col-xs-10" name="extraError{{$questionID}}" {{(empty($customerError)) ? 'disabled':""}} style="{{(empty($customerError)) ? "background-color: gainsboro":""}}">
                            <option disabled selected>--{{trans('common.select')}}--</option>
                            @foreach($baseAllAns[20] as $answerID => $answer)
                            <option {{($customerError == $answerID)?'selected':""}} value="{{$answerID}}">{{trans('error'.'.'.$answer['answerKey'])}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                @if ($errors->any())
                @if($errors->has('question'.$questionID))
                <div class="col-xs-12" style="padding-top: 15px;">
                    <div class="alert-danger">
                        <ul>
                            {{$errors->first('question'.$questionID)}}
                        </ul>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <input type="hidden" class="form-control"  type="text" name="timeStart" value="{{date('Y-m-d H:i:s')}}">

    <div class="col-xs-12 fix-pad " style="text-align: center;padding: 15px;">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#updateSurvey">
            <i class="fas fa-pen-alt bigger-110"></i>
            {{trans($transFile.'.FinishedSurvey')}}
        </button>
    </div>
</form>

<!-- Modal -->
<div class="modal fade" id="updateSurvey" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{trans($transFile.'.Information')}}</h3>
            </div>
            <div class="modal-body">
                <h4>{{trans($transFile.'.DoYouWantToUpdateSurvey')}}</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans($transFile.'.Closed')}}</button>
                <button type="button" class="btn btn-primary" id="btnSubmit">{{trans($transFile.'.Create')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.connection').click(function () {
            var idConnection = $(this).attr('idConnection');
            if ($(this).val() == 4)
            {
                $('#questionBoard').show();
            } else {
                $('#questionBoard').hide();
            }
        });
        $('.question').click(function () {
            var idQuestion = $(this).attr('idQuestion');
            if ($(this).val() == 1 || $(this).val() == 2)
            {
                $('[name=extraAction' + idQuestion + ']').css('background-color', 'white').removeAttr('disabled');
                $('[name=extraError' + idQuestion + ']').css('background-color', 'white').removeAttr('disabled');
            } else {
                $('[name=extraAction' + idQuestion + ']').css('background-color', 'gainsboro').attr('disabled', 'disabled');
                $('[name=extraError' + idQuestion + ']').css('background-color', 'gainsboro').attr('disabled', 'disabled');
            }

            // in the handler, 'this' refers to the box clicked on
            var $box = $(this);
            if ($box.is(":checked")) {
                $('[name=extraAnswer' + idQuestion + ']').css('background-color', 'gainsboro').attr('disabled', 'disabled');
                // the name of the box is retrieved using the .attr() method
                // as it is assumed and expected to be immutable
                var group = "input:checkbox[name='" + $box.attr("name") + "']";
                // the checked state of the group/box on the other hand will change
                // and the current value is retrieved using .prop() method
                $(group).prop("checked", false);
                $box.prop("checked", true);
            } else {
                $('[name=extraAnswer' + idQuestion + ']').css('background-color', 'white').removeAttr('disabled');
                $box.prop("checked", false);
            }

        });
        $('#btnSubmit').click(function () {
            $('#surveyHiFPT').submit();
        });
    })
</script>
