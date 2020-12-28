
<p class="question answer"><b>Số HĐ: {{$contract}}</b></p>
<span style="font-size: 14px;"><b>Số điện thoại liên hệ: {{$contactPhone}}</b></span><br>
<span style="font-size: 14px;"><b>Ghi chú: {{$mainNote}}</b></span>

<?php
//   if (in_array($detailSurvey->question_id, [])$detailSurvey->question_id == 10 || $detailSurvey->question_id == 11 || $detailSurvey->question_id == 12 || $detailSurvey->question_id == 13 || $detailSurvey->question_id == 20 || $detailSurvey->question_id == 21|| $detailSurvey->question_id == 41 || $detailSurvey->question_id == 42|| $detailSurvey->question_id == 46|| $detailSurvey->question_id == 47) {
$arrayCsatService=[10, 11, 12, 13, 20, 21, 41, 42, 46, 47, 49, 50, 51];
$arrayNeedToDo=[5, 7, 25, 40, 44];
if (empty($detail)) {//không có câu trả lời khảo sát
    $arrayResult = [0 => 'Không cần liên hệ', 1 => 'Không liên lạc được', 2 => 'Gặp KH,KH từ chối CS', 3 => 'Không gặp người SD'];
    echo "<br><span style='font-size: 14px;'><b> Kết quả liên hệ:</b>" . $arrayResult[$connected] . "</span>";
} else {
    $j = 0;
    foreach ($detail as $key2 => $value2) {
        if (in_array($value2->question_id, $arrayNeedToDo))
            $j++;
    }
    $i = 0;
    $len = count($detail);
// 1 câu hỏi có nhiều câu tra lời sử 
// chỉ lặp câu hỏi không lặp kết quả trả lờ
    $flag = $tempQuestion = '';
    foreach ($detail as $detailSurvey) {
        if ($detailSurvey->question_id != $flag) {
            $flag = $detailSurvey->question_id;
            echo "<p class='question'>" . $detailSurvey->question_title . "</p>";
        }
        ?>

        <?php
//        $colorText = ($detailSurvey->survey_result_answer_id == '-1') ? 'text-warning' : 'text-primary';
        $colorText = 'text-primary';
        if (is_numeric($detailSurvey->answers_title)) {
            if ($detailSurvey->answers_title >= 0 && $detailSurvey->answers_title <= 6) {
                $detailSurvey->answers_title = $detailSurvey->answers_title . ' (Không ủng hộ)';
            } else if ($detailSurvey->answers_title >= 7 && $detailSurvey->answers_title <= 8) {
                $detailSurvey->answers_title = $detailSurvey->answers_title . ' (Trung lập)';
            } else {
                $detailSurvey->answers_title = $detailSurvey->answers_title . ' (Ủng hộ)';
            }
        } else {
            if (!in_array($detailSurvey->question_id, $arrayNeedToDo))
                $detailSurvey->answers_title = $detailSurvey->survey_result_answer_id . ' (' . $detailSurvey->answers_title . ')';
//            else 
//                 $detailSurvey->answers_title=$detailSurvey->answers_title;
        }

        if (!empty($detailSurvey->survey_result_note)) {
            $detailSurvey->survey_result_note = $detailSurvey->survey_result_note;
        }
//        if ($tempQuestion == $detailSurvey->question_id) {//nếu câu hỏi có nhiều câu trả lời, có ghi chú thì chỉ hiện ghi chú ở 1 câu trả lời
//            $detailSurvey->survey_result_note = '';
//        }
        if (!empty($detailSurvey->answers_extra_title)) {
            $detailSurvey->answers_extra_title = $detailSurvey->answers_extra_title;
        }
        ?>
        <p class="answer {{$colorText}}">
                <?php if ($detailSurvey->survey_result_answer_id != -1) {
                   if(!in_array($detailSurvey->question_id, $arrayNeedToDo))
                    {
                    ?>
                    {{$detailSurvey->answers_title}}
                    <?php
                    }
                    if (in_array($detailSurvey->question_id, $arrayCsatService)) {
                        if (in_array($detailSurvey->survey_result_answer_id, [1,2])) {
                            ?>
                            <br>
                            {{$detailSurvey->answers_extra_title}} <br>
                            {{$detailSurvey->answers_extra_action}} 
                            <?php
                        }
                    }
                    ?>               

                    <?php
                } else {
                    ?>
                    {{ $detailSurvey->answers_extra_title}}
                <?php } ?><?php
                //Không phải câu 5,7 và ko phải cuối vòng lặp
                if (!in_array($detailSurvey->question_id, $arrayNeedToDo) && $i != $len - 1) {
                    ?><?php if ($detailSurvey->survey_result_note != '' && $detailSurvey->survey_result_note != NULL) { ?><br><?php } ?>
                    {{$detailSurvey->survey_result_note}}</b>

                <?php
            }
            //Câu 5, 7, 25 chọn 1 đáp án
            else if ((in_array($detailSurvey->question_id, $arrayNeedToDo)) && $j == 1) {
                ?>
                {{$detailSurvey->answers_title}}</b>
            {{$detailSurvey->survey_result_note}}</b>
            <?php
            //Câu 5, 7, 25 chọn từ 2 đáp án trở lên, ko phải cuối vòng lặp
        } else if ((in_array ($detailSurvey->question_id, $arrayNeedToDo)) && $j >= 2 && $i != $len - 1) {
            ?>

            {{$detailSurvey->answers_title}}</b>
            <?php
        }
        //Câu 5, 7, 25 chọn từ 2 đáp án trở lên, cuối vòng lặp
        else if ((in_array($detailSurvey->question_id, $arrayNeedToDo)) && $j >= 2 && $i == $len - 1) {
            ?>
              {{$detailSurvey->answers_title}}</b>
            {{$detailSurvey->survey_result_note}}</b>
        <?php } ?>

        <!--<br/>-->
        </p>
        <?php
        $tempQuestion = $detailSurvey->question_id;
        $i++;
    }
}
?>
<style>
    .question {
        font-size: medium;
    }
    .answer {
        font-size: large;
    }
</style>