<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Surveys extends Model {

    //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outbound_surveys';

    public function getSurvey($id) {
        $result = DB::table($this->table)
                ->select('survey_id', 'survey_title', 'section_connected', 'section_note', 'section_account_id', 'section_user_id', 'section_note')
                ->join('outbound_survey_sections', 'section_survey_id', '=', 'survey_id')
                ->where('survey_id', '=', $id)
                ->first();
        return $result;
    }

    public function getDetailSurvey($id) {
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

    public function saveSurveySections($surveySections) {
        $id = DB::table('outbound_survey_sections')->insertGetId($surveySections);
        return $id;
    }

    public function getAnswerById($answerID) {
        $result = DB::table('outbound_answers')
                ->select('answer_id', 'answers_title', 'answers_point')
                ->where('answer_id', '=', $answerID)
                ->first();
        return $result;
    }

}
