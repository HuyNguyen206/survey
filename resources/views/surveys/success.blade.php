@extends('layouts.app')

@section('content')

<?php $transFile = 'history'; ?>
<div style="color: black; text-align: left; padding: 10px;" >
    <h2 style="text-align: center">{{trans($transFile.'.Success')}}</h2>
                
    <p class="question answer"><b>{{trans($transFile.'.ContractNumber')}}: {{$contract}}</b></p>
    <span style="font-size: 14px;"><b>{{trans($transFile.'.ContactPersonPhone')}}: {{$contactPhone}}</b></span><br>
    <span style="font-size: 14px;"><b>{{trans($transFile.'.Note')}}: {{$mainNote}}</b></span>

    <?php
        $arrayResult = [
            0 => trans($transFile.'.NoNeedContac'),
            1 => trans($transFile.'.CannotContact'),
            2 => trans($transFile.'.MeetCustomerCustomerDeclinedToTakeSurvey'),
            3 => trans($transFile.'.DidntMeetUser'),
        ];

        $i = 1
    ?>
    @if (empty($detail))
        <br><span style='font-size: 14px;'><b>{{trans($transFile.'.ContactResult')}}:</b>{{$arrayResult[$connected]}}</span>
    @else
        <?php
            $seeFirstAnswerGroup = null;
        ?>
        @foreach ($detail as $detailSurvey)
            <?php
                $questionTitle = $questionKey = $answersTitle = $note = $answersExtraTitle = $answersExtraTitleKey = $answersExtraAction = $answersExtraActionKey = $answersExtraError = $answersExtraErrorKey = null;
                $colorText = 'text-primary';
                $questionTitle = $detailSurvey->question_title;
                $questionKey = $detailSurvey->question_key;
                $answersTitle = $detailSurvey->survey_result_answer_id;
                $answersKey = $detailSurvey->answers_key;
                $questionGroup = $detailSurvey->question_answer_group_id;
                $isNPS = $detailSurvey->question_is_nps;
                if (!empty($detailSurvey->survey_result_note)) {
                    $note = $detailSurvey->survey_result_note;
                }
                if (!empty($detailSurvey->answers_extra_title_key)) {
                    $answersExtraTitle = $detailSurvey->answers_extra_title_key;
                }
                if (!empty($detailSurvey->answers_extra_action_key)) {
                    $answersExtraAction = $detailSurvey->answers_extra_action_key;
                }
                if (!empty($detailSurvey->answers_extra_error_key)) {
                    $answersExtraError = $detailSurvey->answers_extra_error_key;
                }
            ?>
            @if($seeFirstAnswerGroup != $detailSurvey->question_id)
                <p class='question'>{{trans('common.Question').' '.$i++}}: {{trans('question.'.$questionKey)}}?</p>
            @endif
            <p class="answer {{$colorText}} col-xs-4">
                @if($answersTitle == -1 || $isNPS)
                    -- {{trans('answer.'.$answersKey)}} <br>
                @else
                    -- {{trans('answer.'.$answersKey)}}({{$answersTitle}}) <br>
                @endif
            </p>
            <p class="answer {{$colorText}} col-xs-8">
                @if($seeFirstAnswerGroup != $detailSurvey->question_id)
                    @if(!empty($answersExtraTitle))
                        {{trans('answer.'.$answersExtraTitle)}} <br>
                    @endif

                    @if(!empty($answersExtraAction))
                        {{trans('action.'.$answersExtraAction)}} <br>
                    @endif

                    @if(!empty($answersExtraError))
                        {{trans('error.'.$answersExtraError)}} <br>
                    @endif

                    @if(!empty($note))
                        {{trans('common.Note')}}: {{$note}} <br>
                    @endif
                @endif
            </p>
            <br style="clear: both;">
            <?php $seeFirstAnswerGroup = $detailSurvey->question_id;?>
        @endforeach
    @endif

    <div style="text-align: center">
        <button type="button" class="btn btn-primary" ><a style="color: white" href="<?php echo url('/'); ?>">{{trans($transFile.'.BackToSurveyHistory')}}</a></button>
    </div>
</div>

<style>
    .question {
        font-size: medium;
    }
    .answer {
        font-size: large;
    }
</style>
@stop