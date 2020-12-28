<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SurveySections;
use DB;
use Exception;

class SurveyResult extends Model {

    //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outbound_survey_result';
    protected $primaryKey = 'survey_result_id';
    public $timestamps = false;

    public function getSurvey($id) {
        $result = DB::table($this->table)
                ->where('survey_id', '=', $id)
                ->first();
        return $result;
    }

    public function getQuestionBySurvey($surveyID) {
        $result = DB::table('outbound_questions')
                ->where('question_survey_id', '=', $surveyID)
                ->get();
        return $result;
    }

    public function saveSurveyResult($SurveyResult) {
        $id = DB::table($this->table)->insertGetId($SurveyResult);
        return $id;
    }

    public function getDetailSurvey($surveyID) {
        $result = DB::table('outbound_survey_result')
                ->select('*')
                ->where('survey_result_section_id', '=', $surveyID)
                ->get();
        return $result;
    }

    public function updateDetailSurvey($idSurvey, $resultUpdate, $typeSurvey, $arrayAnswer, $section_con = '') {
        DB::beginTransaction();
        //Nếu retry khảo sát lại, insert dữ liệu mới
        $gopy = 0;
        $i = 0;
        $arrayIdQuestion = explode(' ', $arrayAnswer);
        array_pop($arrayIdQuestion);
        $len = count($arrayIdQuestion);
        foreach ($arrayIdQuestion as $key => $value) {
            $anserextraActionId = NULL;
            $extraQues = NULL;
            if ($value == "5" || $value == "7" || $value == "25" || $value == "40" || $value == "44") {
                //Edit
                $gopy = 1;
                if ($section_con == 4) {
                    if (!is_array($resultUpdate['answer' . $value]))
                        $answer = -1;
                    else {
                        $answer = array();
//                            $arrayResult = implode(',', $resultUpdate['answer' . $value->survey_result_question_id]);
                        foreach ($resultUpdate['answer' . $value] as $key => $value2) {
                            if ($value2 != false && $value2 != -1) {
                                array_push($answer, $value2);
                            }
                        }
                        if (count($answer) > 0) {
                            $answer = implode(',', $answer);
                        } else {
                            $answer = -1;
                        }
                    }
                }
                //Retry
                else {
                    if (isset($resultUpdate['answer' . $value])) {
                        $answer = implode(',', $resultUpdate['answer' . $value]);
                    } else
                        $answer = -1;
                }
                $subnote = isset($resultUpdate['subnote' . $value]) ? $resultUpdate['subnote' . $value] : NULL;
                $extraQues = isset($resultUpdate['extraQuestion' . $value]) ? $resultUpdate['extraQuestion' . $value] : NULL;
//                    var_dump($answer.','.$subnote.','.$extraQues);
//                    die;
            } else {

//                    $answer = isset($resultUpdate['answer' . $value]) ? $resultUpdate['answer' . $value] : -1;
                $answer = (!isset($resultUpdate['answer' . $value]) || $resultUpdate['answer' . $value] == 0) ? -1 : $resultUpdate['answer' . $value];
                $subnote = isset($resultUpdate['subnote' . $value]) ? $resultUpdate['subnote' . $value] : NULL;
                $extraQues = isset($resultUpdate['extraQuestion' . $value]) ? $resultUpdate['extraQuestion' . $value] : NULL;
            }
            $valueint = (int) $value;
            //Vừa chấm điểm vừa chọn lý do 
            if ($extraQues != NULL) {
                if ($answer != -1)
                    $extraQues = NULL;
            }
            if ($valueint == 10 || $valueint == 11 || $valueint == 12 || $valueint == 13 || $valueint == 20 || $valueint == 21 || $valueint == 41 || $valueint == 42 || $valueint == 46 || $valueint == 47) {
                //Chọn câu trả lời
                if ($answer != -1) {
                    if ($answer == 1 || $answer == 2) {
                        $extraQues = $resultUpdate['extraError' . $valueint];
                        $anserextraActionId = $resultUpdate['extraAction' . $valueint];
                    }
                }
            }
            //vòng lặp cuối
            if ($i == $len - 1) {
                //Chưa chọn
                if ($gopy == 0) {
                    $valueintplus = ($typeSurvey == 1) ? 7 : ($typeSurvey == 2 ? 5 : ($typeSurvey == 6 ? 25 : ($typeSurvey == 9 ? 40 : 44)));
                    $answerplus = -1;
                    $subnoteplus = NULL;
                    $anserextraActionIdplus = NULL;
                    $extraQuesplus = NULL;
                    $resultPlus = DB::table('outbound_survey_result')->insert([
                            ['survey_result_section_id' => $idSurvey,
                            'survey_result_question_id' => $valueintplus,
                            'survey_result_answer_id' => $answerplus,
                            'survey_result_answer_extra_id' => $extraQuesplus,
                            'survey_result_note' => $subnoteplus,
                            'survey_result_action' => $anserextraActionIdplus
                        ],
                    ]);
                }
            }
            $anserextraActionId = isset($anserextraActionId) ? $anserextraActionId : NULL;
            $result = DB::table('outbound_survey_result')->insert([
                    ['survey_result_section_id' => $idSurvey,
                    'survey_result_question_id' => $valueint,
                    'survey_result_answer_id' => $answer,
                    'survey_result_answer_extra_id' => $extraQues,
                    'survey_result_note' => $subnote,
                    'survey_result_action' => $anserextraActionId
                ],
            ]);
            if (!$result) {
                DB::rollback();
                throw new Exception(null, 500, null);
            }
            $i++;
        }

        DB::commit();
        return 'Khảo sát lại thành công';
//        }
    }

    public function checkIsEva($accountId) {

        $hasNPS = FALSE;
//            if (isset($responseInfo['accountInfoFromSurvey']->id)) {
//                $SurveySections = new SurveySections();
        $SurveySections = new SurveySections();
        $accountInfoFromSurvey = $SurveySections->getAllSurveyInfoOfAccount($accountId);

        $historyOutboundSurvey = array();
        $dateSurveyTemp = FALSE;

        foreach ($accountInfoFromSurvey as $i) {
            // chi tiết từng khảo sát
            $i->resultDetail = $SurveySections->getAllDetailSurveyInfo($i->section_id);


            // kiểm tra câu hỏi NPS
            // nếu câu hỏi có NPS lấy thời gian hoàn so sánh với thời gian hoàn thanh NPS của các câu khảo sát khác.

            $content = '';
            $temp = $i->resultDetail;
            foreach ($i->resultDetail as $d) {
//        			var_dump($i->resultDetail);die;
                $flag = NULL;

                if ($d->question_id != $flag) {
                    $flag = $d->question_id;
                    $content .= '<b>' . $d->question_title_short . ': </b>';
                }
                $content .= $d->answers_title . ", ";
//        			if(($d->question_is_nps==1) && isset($d->question_is_nps)){
                if ($d->question_is_nps == 1) {
                    if ($i->section_time_completed > $dateSurveyTemp) {
                        $dateSurveyTemp = $i->section_time_completed;
                    }
                }
            }
            $i->content = $content;
            //$i->resultDetail = $temp;

            $historyOutboundSurvey[] = (array) $i;
        }
        $responseInfo['last_nps_time'] = $dateSurveyTemp;



        if ($dateSurveyTemp != FALSE) {

            $currentDate = new \DateTime();
            $lastest_survey_nps_time = new \DateTime($dateSurveyTemp);
            $interval = $lastest_survey_nps_time->diff($currentDate)->format("%a");
//        		if( $currentDate < $dateSurveyTemp + 90 ){
            if ($interval < 90) {
//         		$dateCompleted = $i->section_time_completed;
//         		$dateCompleted = date_create($dateCompleted);
//         		date_add($dateCompleted, date_interval_create_from_date_string('90 days'));
//         		if ( $dateCompleted > $currentDate ){
                $hasNPS = TRUE;
                //}
            }
        }


        return $hasNPS;
//        
//        if ($type == 1) {
//            $result = DB::table('outbound_survey_result')
//                    ->select('*')
//                    ->where('survey_result_section_id', '=', $idSurvey)
//                    ->Where(function ($query) {
//                        $query->where('survey_result_question_id', '=', 7)
//                        ->orWhere('survey_result_question_id', '=', 6);
//                    })
//                    ->get();
//        } else {
//            $result = DB::table('outbound_survey_result')
//                    ->select('*')
//                    ->where('survey_result_section_id', '=', $idSurvey)
//                    ->Where(function ($query) {
//                        $query->where('survey_result_question_id', '=', 5)
//                        ->orWhere('survey_result_question_id', '=', 8);
//                    })
//                    ->get();
//        }
//        if (empty($result)) {
//            return true;
//        } else
//            return false;
    }

    public function apiGetInfoSurveySalaryIBB($question_id, $answer_id, $date_start, $date_end) {
        $result = DB::table('outbound_survey_result as osr')
                ->join('outbound_survey_sections as oss', 'oss.section_id', '=', 'osr.survey_result_section_id')
                ->join('outbound_answers as oa', 'oa.answer_id', '=', 'osr.survey_result_answer_id')
                ->join('outbound_accounts as oac', 'oac.contract_num', '=', 'oss.section_contract_num')
                ->whereRaw('osr.survey_result_question_id in (' . $question_id . ')')
                ->whereRaw('osr.survey_result_answer_id in (' . $answer_id . ')')
                ->where('oss.section_time_completed_int', '>=', strtotime(date_format($date_start, 'Y-m-d 00:00:00')))
                ->where('oss.section_time_completed_int', '<=', strtotime(date_format($date_end, 'Y-m-d 23:59:59')))
                ->select('oss.section_contract_num', 'oa.answers_point', 'oss.section_time_completed as section_time_start', 'oac.objid')
                ->get();
        return $result;
    }

    public function apiGetInfoSurveySalaryTinPNC($question_id, $answer_id, $date_start, $date_end) {
        $result = DB::table('outbound_survey_result as osr')
                ->join('outbound_survey_sections as oss', 'oss.section_id', '=', 'osr.survey_result_section_id')
                ->join('outbound_answers as oa', 'oa.answer_id', '=', 'osr.survey_result_answer_id')
                ->join('outbound_accounts as oac', 'oac.id', '=', 'oss.section_account_id')
                ->whereRaw('osr.survey_result_question_id in (' . $question_id . ')')
                ->whereRaw('osr.survey_result_answer_id in (' . $answer_id . ')')
                ->where('oss.section_time_completed_int', '>=', strtotime(date_format($date_start, 'Y-m-d 00:00:00')))
                ->where('oss.section_time_completed_int', '<=', strtotime(date_format($date_end, 'Y-m-d 23:59:59')))
                ->select('oac.objid as objId', 'oss.section_contract_num as contract', 'oa.answers_point as point', 'oss.section_time_start as time', 'oss.section_supporter as supporter', 'oss.section_subsupporter as subSupporter', 'oss.section_code as code', 'oss.section_survey_id', 'oss.section_account_inf as accDeploy', 'oss.section_account_list as accMaintaince')
                ->get();
        return $result;
    }

    public function apiGetInfoSurveySalaryTinPNCAndNetTV($question_id, $answer_id, $date_start, $date_end) {
        $result = DB::table('outbound_survey_result as osr')
                ->join('outbound_survey_sections as oss', 'oss.section_id', '=', 'osr.survey_result_section_id')
                ->join('outbound_answers as oa', 'oa.answer_id', '=', 'osr.survey_result_answer_id')
                ->join('outbound_accounts as oac', 'oac.id', '=', 'oss.section_account_id')
                ->where('oss.section_time_completed_int', '>=', strtotime(date_format($date_start, 'Y-m-d 00:00:00')))
                ->where('oss.section_time_completed_int', '<=', strtotime(date_format($date_end, 'Y-m-d 23:59:59')))
                ->whereIn('osr.survey_result_question_id', $question_id)
                ->whereIn('osr.survey_result_answer_id', $answer_id)
                ->select('osr.survey_result_question_id', 'oac.objid as objId', 'oss.section_contract_num as contract', 'oa.answers_point as point', 'oss.section_time_start as time', 'oss.section_supporter as supporter', 'oss.section_subsupporter as subSupporter', 'oss.section_code as code', 'oss.section_survey_id', 'oss.section_account_inf as accDeploy', 'oss.section_account_list as accMaintaince')
                ->get();
        return $result;
    }

    public static function deleteSurveyResult($sectionID) {
        DB::table('outbound_survey_result')->where('survey_result_section_id', '=', $sectionID)->delete();
    }

    public function insertSurveyResult($param){
        $resultID = DB::table($this->table)->insert($param);
        return $resultID;
    }
}
