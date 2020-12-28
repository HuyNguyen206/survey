<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
//use Eloquent;
use App\Models\User;
use App\Models\Apiisc;
use Exception;

class SurveySections extends Model {

    protected $table = 'outbound_survey_sections';
    protected $primaryKey = 'section_id';
    public $timestamps = false;

    public function getAllSurveyInfoUser($userId, $itemPer, $pageNum, $filter, $listIdResult, $listTypeSurvey) {
        if ($pageNum != '')
            $offset = ($pageNum - 1) * $itemPer;
        $result = DB::table('outbound_survey_sections AS s')
                ->select(DB::raw("s.section_id,s.section_contract_num,s.section_customer_name,s.section_connected,s.section_note,s.section_action,s.section_survey_id,s.section_user_name,s.section_time_completed"))
                ->where('s.section_user_id', '=', $userId)
                ->where('s.section_time_completed_int', '>=', strtotime($filter['startDate'] . ' 00:00:00'))
                ->where('s.section_time_completed_int', '<=',  strtotime($filter['endDate'] . ' 23:59:59'))
                ->where(function($query) use ($filter, $listIdResult, $listTypeSurvey) {
                    if (isset($filter['contract']) && ($filter['contract'] != '')) {
                        $query->where('s.section_contract_num', '=', $filter['contract']);
                    } else {
                        if (!empty($listIdResult)) {
                            $query->whereIn('section_connected',$listIdResult);
                        }
                        if (!empty($listTypeSurvey)) {
                            $query->whereIn('section_survey_id', $listTypeSurvey);
                        }
                    }
                });
        if ($pageNum != '') {
            $result = $result->orderBy('section_time_start', 'DESC')
                    ->skip($offset)
                    ->take($itemPer)
                    ->get();
//            DB::enableQueryLog();
//            dd($result->orderBy('section_time_start', 'DESC')
//                            ->skip($offset)
//                            ->take($itemPer)
//                            ->toSql());

            $currentDate = new \DateTime();
            $arraySurvey = array();
            $roleID = User::getRole($userId);
            $timeLimit = ($roleID == 36) ? 'P30D' : 'PT5M';
//        $messageEdit = ($roleID == 2) ? 'Khảo sát này đã vượt quá 30 ngày để sửa' : 'Khảo sát này đã vượt quá 5 phút để sửa';
//        $messageRetry = ($roleID == 2) ? 'Khảo sát này đã vượt quá 30 ngày để khảo sát lại' : 'Khảo sát này đã vượt quá 5 phút để khảo sát lại';
            foreach ($result as $key => $value) {
                $value = (array) $value;
                if ($value['section_connected'] == 4) {
                    $time_complete = new \DateTime($value['section_time_completed']);
                    $time_complete->add(new \DateInterval($timeLimit));
                    if ($time_complete >= $currentDate) {
                        $value['edit'] = 1;
                    } else {
                        $value['edit'] = 2;
                    }
                    $value['retry'] = 3;
                } else {
//                $currentDate = new \DateTime();
//                $time_complete = new \DateTime($value['section_time_completed']);
//                $time_complete->add(new \DateInterval($timeLimit));
//                if ($time_complete < $currentDate) {
//                    $value['retry'] = 2;
//                } else {
                    $value['retry'] = 1;
//                }
//                if($value['section_count_connected']<=2)
//                    $value['retry']=1;
//                else  $value['retry']=2;
                    $value['edit'] = 3;
                }
                $value = (object) $value;
                array_push($arraySurvey, $value);
            }
            return $arraySurvey;
        } else {
            $result2 = $result
                    ->get();
            return count($result2);
        }
    }
    public function getAllSurveyInfoOfAccount($accountID, $surveySectionID = null) {
        $query = DB::table('outbound_survey_sections AS survey_sections')
                ->select('survey_sections.section_time_start', 'survey_sections.section_time_completed', 'survey_sections.section_action', 'survey_sections.section_connected', 'survey_sections.section_survey_id', 'survey_sections.section_id', 'survey_sections.section_user_name')
                ->where('survey_sections.section_account_id', '=', $accountID)
                ->whereIn('survey_sections.section_survey_id', [1, 2, 6, 9, 10])
                ->orderBy('survey_sections.section_time_start', 'DESC');
        if(!empty($surveySectionID)){
            $query->whereNotIn('survey_sections.section_id', [$surveySectionID]);
        }
        $result = $query->get();
        return $result;
    }

    public function getAllSurveyInfoOfAccountQGD($contractNum) {
        $result = DB::table('outbound_survey_sections AS survey_sections')
                ->leftJoin('outbound_surveys AS survey', 'survey.survey_id', '=', 'survey_sections.section_survey_id')
//            ->join('users', 'users.id', '=', 'survey_sections.section_user_id')
                ->select('survey_sections.section_time_start', 'survey_sections.section_time_completed', 'survey_sections.section_action', 'survey_sections.section_connected', 'survey_sections.section_survey_id', 'survey_sections.section_id', 'survey.survey_title', 'survey_sections.section_user_name')
                ->where('survey_sections.section_contract_num', '=', $contractNum)->Where('survey_sections.section_survey_id', '=', 4)
                ->orderBy('survey_sections.section_time_start', 'DESC')
                ->get();
        return $result;
    }

    public function getAllDetailSurveyInfo($id) {
        $result = DB::table('outbound_survey_result AS survey_result')
                ->join('outbound_questions AS questions', 'questions.question_id', '=', 'survey_result.survey_result_question_id')
                ->join('outbound_survey_sections', 'outbound_survey_sections.section_id', '=', 'survey_result.survey_result_section_id')
                ->join('outbound_answers AS answers', DB::raw('1'), '=', DB::raw('1'))
                ->leftJoin('outbound_answers AS answers1', 'answers1.answer_id', '=', 'survey_result.survey_result_answer_extra_id')
                ->leftJoin('outbound_answers AS answers2', 'answers2.answer_id', '=', 'survey_result.survey_result_action')
                ->leftJoin('outbound_answers AS answers3', 'answers3.answer_id', '=', 'survey_result.survey_result_error')
                ->select('question_id', 'question_answer_group_id', 'question_title', 'question_title_short', 'question_note', 'question_key','survey_result_answer_id', 'survey_result_note','question_is_nps', 'question_orderby',
                    DB::raw('answers.answers_title AS answers_title, answers.answers_key AS answers_key'),
                    DB::raw('answers1.answers_title AS answers_extra_title, answers1.answers_key AS answers_extra_title_key'),
                    DB::raw('answers2.answers_title AS answers_extra_action, answers2.answers_key AS answers_extra_action_key'),
                    DB::raw('answers3.answers_title AS answers_extra_error, answers3.answers_key AS answers_extra_error_key'),
                    'section_connected', 'section_contract_num', 'section_note', 'section_contact_phone'
                )
                ->where('question_active', '=', '1')
                ->whereRaw('FIND_IN_SET(answers.answer_id, survey_result.survey_result_answer_id)')
                ->where(['survey_result_section_id' => $id])
                ->orderBy('question_orderby', 'asc')
                ->get();
        return $result;
    }

    public function checkSurvey($userID, $surveyID, $roleID) {
        $result = DB::table('outbound_survey_sections')->select('*')->where('section_id', '=', $surveyID)->get();
        if (empty($result))
            return 1;
        else {
            $result = $result[0];

            //Nếu người dùng không có quyền chỉnh sửa, retry    
            if ($roleID != 36 && $result->section_user_id != $userID)
                return 2;
            else
                return $result;
        }
    }

    public function checkExistCodes($codes, $type, $soHD) {
        $result = DB::table('outbound_survey_sections')->select('*')
                ->where('section_code', '=', $codes)
                ->where('section_survey_id', '=', $type)
                ->where('section_contract_num', '=', $soHD)
                ->get();
        return $result;
    }

    public function checkSurveyApiUpgrade($shd) {

        $start = date_create(date('Y-m-d H:i:s'));
        date_add($start, date_interval_create_from_date_string('-1 months'));
        $result = DB::table('outbound_survey_sections as oss')
                ->join('outbound_survey_result as osr', 'oss.section_id', '=', 'osr.survey_result_section_id')
                ->select('oss.section_id', 'osr.survey_result_question_id', 'osr.survey_result_answer_id')
                ->where('oss.section_contract_num', '=', $shd)
                ->where('oss.section_time_completed', '>=', $start)
                ->get();
        if (!empty($result)) {
            return true;
        }

        date_add($start, date_interval_create_from_date_string('-2 months'));
        $res = DB::table('outbound_survey_sections as oss')
                ->join('outbound_survey_result as osr', 'oss.section_id', '=', 'osr.survey_result_section_id')
                ->select('oss.section_id', 'osr.survey_result_question_id', 'osr.survey_result_answer_id')
                ->where('oss.section_contract_num', '=', $shd)
                ->where('oss.section_time_completed', '>=', $start)
                ->get();
        if (empty($res))
            return false;
        else {
            return $res;
        }
    }

    public function getFullQA($surveyID) {
        $result = DB::table('outbound_questions as oq')
            ->join('outbound_answers as oa', 'oa.answer_group', '=','oq.question_answer_group_id')
            ->where('oq.question_survey_id','=',$surveyID)
            ->orderBy('oq.question_orderby')
            ->get();
        return $result;
    }

    public function getAnswerOfNPSOther() {
        $result = DB::table('outbound_answers_group as oag')
            ->join('outbound_answers as oa', 'oa.answer_group', '=','oag.answers_group_id')
            ->where('oag.answers_group_is_other_nps','=',1)
            ->orderBy('oag.answers_group_id')
            ->get();
        return $result;
    }

    public function getQuestionOfNPSOther($surveyID) {
        $result = DB::table('outbound_questions as oq')
            ->where('oq.question_survey_id','=',$surveyID)
            ->where('oq.question_alias','=',9)
            ->orderBy('oq.question_orderby')
            ->get();
        return $result;
    }

    public function getSurvey() {
        $result = DB::table('outbound_surveys')
                ->where('survey_deleted', '=', '0')
                ->where('survey_active', '=', '1')
                ->select('survey_id', 'survey_type', 'survey_title', 'survey_description')
                ->get();
        return $result;
    }

    public function getQuest() {
        $result = DB::table('outbound_questions')
                ->where('question_active', '=', '1')
                ->select('question_id', 'question_type', 'question_survey_id'
                        , 'question_answer_group_id', 'question_answer_group_extra_id', 'question_title', 'question_title_short', 'question_orderby', 'question_note', 'question_is_nps', 'question_group_service')
                ->get();
        return $result;
    }

    public function getAnswer() {
        $result = DB::table('outbound_answers')
                ->get();
        return $result;
    }

    public function getAnswerGroup() {
        $result = DB::table('outbound_answers_group')
                ->select('answers_group_id', 'answers_group_title')
                ->get();
        return $result;
    }

    public function getAnswerOther() {
        $result = DB::table('outbound_answer_other')
                ->select('other_id', 'other_title', 'other_answer_id', 'other_position')
                ->get();
        return $result;
    }

    public function getNeedSurveySendEmail() {
        $result = DB::table('outbound_answers_group')
                ->join('', '', '')
                ->select('answers_group_id', 'answers_group_title')
                ->get();
        return $result;
    }

    ///Lay tat ca chi nhanh
    public function getLocation() {
        $result = DB::table('location')
                ->select('id', 'name')
                ->get();
        return $result;
    }

    public function getSurveySections($param) {
        $sql = DB::table($this->table . ' as oss');
        if (isset($param['sectionId'])) {
            $sql->where('oss.section_id', '=', $param['sectionId']);
        }
        if (isset($param['num_type'])) {
            $sql->where('oss.section_survey_id', '=', $param['num_type']);
        }
        if (isset($param['code'])) {
            $sql->where('oss.section_code', '=', $param['code']);
        }
        if (isset($param['shd'])) {
            $sql->where('oss.section_contract_num', '=', $param['shd']);
        }
        $result = $sql->first();
        return $result;
    }

    public function getSurveySectionsAndResultHaveCheckList($param) {
        $sql = DB::table($this->table . ' as oss')
                ->join('outbound_survey_result as osr', 'oss.section_id', '=', 'osr.survey_result_section_id');
        $sql->join('checklist as cl', function($join) {
            $join->on('oss.section_survey_id', '=', 'cl.section_survey_id');
            $join->on('oss.section_code', '=', 'cl.section_code');
            $join->on('oss.section_contract_num', '=', 'cl.section_contract_num');
        });
        if (isset($param['sectionId'])) {
            $sql->where('oss.section_id', '=', $param['sectionId']);
        }
        if (isset($param['num_type'])) {
            $sql->where('oss.section_survey_id', '=', $param['num_type']);
        }
        if (isset($param['code'])) {
            $sql->where('oss.section_code', '=', $param['code']);
        }
        if (isset($param['shd'])) {
            $sql->where('oss.section_contract_num', '=', $param['shd']);
        }
        $result = $sql->get();
        return $result;
    }

    public function getSurveySectionsAndResult($param) {
        $sql = DB::table($this->table . ' as oss')
            ->join('outbound_survey_result as osr', 'oss.section_id', '=', 'osr.survey_result_section_id');
        if (isset($param['sectionId'])) {
            $sql->where('oss.section_id', '=', $param['sectionId']);
        }
        if (isset($param['num_type'])) {
            $sql->where('oss.section_survey_id', '=', $param['num_type']);
        }
        if (isset($param['code'])) {
            $sql->where('oss.section_code', '=', $param['code']);
        }
        if (isset($param['shd'])) {
            $sql->where('oss.section_contract_num', '=', $param['shd']);
        }
        $result = $sql->get();
        return $result;
    }

    public function getSurveySectionsWithEmailTransaction($param) {
        $sql = DB::table($this->table . ' as oss')
                ->leftjoin('outbound_survey_sections_email as osse', 'oss.section_id', '=', 'osse.section_id');
        if (isset($param['sectionId'])) {
            $sql->where('oss.section_id', '=', $param['sectionId']);
        }
        if (isset($param['num_type'])) {
            $sql->where('oss.section_survey_id', '=', $param['num_type']);
        }
        if (isset($param['code'])) {
            $sql->where('oss.section_code', '=', $param['code']);
        }
        if (isset($param['shd'])) {
            $sql->where('oss.section_contract_num', '=', $param['shd']);
        }
        $result = $sql->first();
        return $result;
    }

    // Lưu thông tin và trả lại thông tin
    public function insertSurveyAndGetID($param){
        $resultID = DB::table($this->table)->insertGetId($param);
        return $resultID;
    }

    public function updateSurvey($id, $param){
        $result = DB::table($this->table)
            ->where($this->primaryKey, $id)
            ->update($param);
        return $result;
    }

    public function countListSurvey($condition) {
        $mainQuery = DB::table($this->table . ' as s')
            ->select("s.section_id")
            ->where(function($query) use ($condition) {
                if (!empty($condition['surveyFromInt']) && !empty($condition['surveyToInt'])) {
                    $query->where('s.section_time_completed_int', '>=', $condition['surveyFromInt']);
                    $query->where('s.section_time_completed_int', '<=', $condition['surveyToInt']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['contractNum'])) {
                    $query->where('s.section_contract_num', '=', $condition['contractNum']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['surveyType'])) {
                    $query->whereIn('s.section_survey_id', $condition['surveyType']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['surveyUser'])) {
                    $query->where('s.section_user_name', $condition['surveyUser']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['sectionConnected'])) {
                    $query->whereIn('s.section_connected', $condition['sectionConnected']);
                }
            })
        ;

        $result = $mainQuery->count();
        return $result;
    }

    public function searchListSurvey($condition, $numberPage) {
        $mainQuery = DB::table($this->table . ' as s');
        $raw = "distinct(s.section_id),s.section_subsupporter, s.section_supporter, s.section_acc_sale AS salename, s.section_survey_id, s.section_connected,"
            . " s.section_action, s.section_user_name, s.section_sub_parent_desc, s.section_location, s.section_note, s.section_location_id,"
            . " s.section_time_start, s.section_time_completed, s.section_code,s.section_contract_num, s.section_contact_phone,"
            . " s.section_branch_code, s.section_sale_branch_code, s.section_count_connected,"
            . " s.violation_status";

        $mainQuery->select(DB::raw($raw));
        $mainQuery->where(function($query) use ($condition) {
                if (!empty($condition['surveyFromInt']) && !empty($condition['surveyToInt'])) {
                    $query->where('s.section_time_completed_int', '>=', $condition['surveyFromInt']);
                    $query->where('s.section_time_completed_int', '<=', $condition['surveyToInt']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['contractNum'])) {
                    $query->where('s.section_contract_num', '=', $condition['contractNum']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['surveyType'])) {
                    $query->whereIn('s.section_survey_id', $condition['surveyType']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['surveyUser'])) {
                    $query->where('s.section_user_name', $condition['surveyUser']);
                }
            })
            ->where(function($query) use ($condition) {
                if (!empty($condition['sectionConnected'])) {
                    $query->whereIn('s.section_connected', $condition['sectionConnected']);
                }
            })
        ;
        $mainQuery->orderBy('s.section_time_completed_int', 'DESC');
        if (!empty($condition['recordPerPage'])) {
            $mainQuery->take($condition['recordPerPage'])->skip($numberPage * $condition['recordPerPage']);
        }
        $result = $mainQuery->get();
        return $result;
    }
}
