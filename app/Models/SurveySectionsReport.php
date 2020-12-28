<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SurveySectionsReport extends Model {
    protected $table = 'survey_section_report';
    protected $primaryKey = 'section_id';
	protected $middleId = '300000';
	protected $dateRunCronUpdate = '2016-08-24 14:00:00';
	
	public function insertSurveySectionReport($param){
		$resIns = DB::table($this->table)->insert($param);
		return $resIns;
	}
	
	public function updateSurveySectionReport($param){
		$resIns = DB::table($this->table)
			->where('section_id', $param['section_id'])
			->update($param);
		return $resIns;
	}
	
	public function getInfoForReport($sectionId){
		$result = DB::table('outbound_survey_sections as ss')
				 ->join('outbound_accounts as os', 'os.id' ,'=', 'ss.section_account_id')
                ->select('*')
                ->where('section_id', '=', $sectionId)
                ->first();
        return $result;
	}
	
	public function getInfoNPS($sectionId, $surveyId){
		if ($surveyId == '1') { // triển khai
            $subSelectRaw = '( select a.answers_point
				from outbound_survey_result r
				join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                WHERE r.survey_result_answer_id in ("6","7","8","9","10","11","12","13","14","15","21") 
                                    AND r.survey_result_question_id = 6 
                                    AND r.survey_result_section_id = s.section_id
				 ) as point,
    			( select survey_result_answer_id
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_question_id = 7
				) as nps_improvement,
                ( select survey_result_note
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_question_id = 7
				) as nps_improvement_note,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 1 
				) as kinhdoanh,	
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 2 
				) as kythuat,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 10 
				) as internet,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 11
				) as truyenhinh
    	';
        } else if($surveyId == '2') { // bảo rì
            $subSelectRaw = '( select a.answers_point
				from outbound_survey_result r
				join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                WHERE r.survey_result_answer_id in ("6","7","8","9","10","11","12","13","14","15","21")
                                    AND r.survey_result_question_id = 8
                                    AND r.survey_result_section_id = s.section_id
				 ) as point,
    			( select survey_result_answer_id
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_question_id = 5
				) as nps_improvement,
                ( select survey_result_note
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_question_id = 5
				) as nps_improvement_note,
    		(select "") as kinhdoanh,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 4
				) as kythuat,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 12
				) as internet,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 13
				) as truyenhinh
    		
    	';
        } else if($surveyId == '3') {//mobi pay
            $subSelectRaw = '( select a.answers_point
				from outbound_survey_result r
				join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                WHERE r.survey_result_answer_id in ("6","7","8","9","10","11","12","13","14","15","21")
                                    AND r.survey_result_question_id = 16
                                    AND r.survey_result_section_id = s.section_id
				 ) as point,
    			( select survey_result_answer_id
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_question_id = 17
				) as nps_improvement,
                ( select "") as nps_improvement_note,
                ( select "") as kinhdoanh,
    			( select "") as kythuat,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 14
				) as internet,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 15
				) as truyenhinh
    		
    	';
        } else if ($surveyId == '6') {//telesales
            $subSelectRaw = '( select a.answers_point
				from outbound_survey_result r
				join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                WHERE r.survey_result_answer_id in ("6","7","8","9","10","11","12","13","14","15","21")
                                    AND r.survey_result_question_id = 24
                                    AND r.survey_result_section_id = s.section_id
				 ) as point,
    			( select survey_result_answer_id
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_question_id = 25
				) as nps_improvement,
                ( select survey_result_note
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_question_id = 25
				) as nps_improvement_note,
                ( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
					 AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 23
				) as kinhdoanh,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 22
				) as kythuat,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 20
				) as internet,
    			( select a.answers_point
					from outbound_survey_result r
					join outbound_answers a on a.answer_id = r.survey_result_answer_id
                                        WHERE s.section_id = r.survey_result_section_id
                                            AND r.survey_result_answer_id in ("1","2","3","4","5") AND r.survey_result_question_id = 21
				) as truyenhinh
    		
    	';
        }
        $result = DB::table('outbound_survey_sections as s')
           ->join('outbound_survey_result as osr', 'osr.survey_result_section_id', '=', 's.section_id')
			->join('outbound_questions as oq','oq.question_id','=','osr.survey_result_question_id')
			->join('outbound_answers as oa','oa.answer_id','=','osr.survey_result_answer_id')
			->select(DB::raw(
					"osr.survey_result_question_id as question,"
					. "osr.survey_result_answer_id as answer,"
					. "osr.survey_result_answer_extra_id as answer_extra_id,"
					. "osr.survey_result_note as note,"
                    . "osr.survey_result_action,"
					."s.section_subsupporter,"
					. "s.section_supporter,"
					. "s.section_acc_sale,"
					. "s.section_survey_id,"
					. "s.section_connected,"
					. "s.section_action, s.section_user_name,"
					. "s.section_sub_parent_desc, s.section_location,"
					. "s.section_note, s.section_time_completed,"
					. "s.section_time_start, s.section_id,"
					. "s.section_code,s.section_contract_num,"
					. "s.section_contact_phone, s.section_branch_code, " . $subSelectRaw))		
			->where('s.section_id','=', $sectionId)
//            ->groupBy('section_code')
			->get();
        return $result;
	}
	
	public function getMaxIDTop(){
		$result = DB::table($this->table)
                ->selectRaw('max(section_id) as section_id')
				->where('section_id', '>=', $this->middleId)
                ->first();
        return $result;
	}
	
	public function getMaxIDMiddle(){
		$result = DB::table($this->table)
                ->selectRaw('max(section_id) as section_id')
				->where('section_id', '<', $this->middleId)
                ->first();
        return $result;
	}
	
	public function getSurveySectionForTranferTop($maxId = null, $limit = 0){
		$result = DB::table('outbound_survey_sections')
                ->select('section_id')
				->orderBy('section_id');
		if($limit > 0){
			$result->limit($limit);
		}
		if(!empty($maxId)){
			$result->where('section_id','>',$maxId);
		}
		$result->where('section_id','>=',$this->middleId);
		
        return $result->get();
	}
	
	public function getSurveySectionForTranferMiddle($maxId = null, $limit = 0){
		$result = DB::table('outbound_survey_sections')
                ->select('section_id')
				->orderBy('section_id');
		if($limit > 0){
			$result->limit($limit);
		}
		if(!empty($maxId)){
			$result->where('section_id','>',$maxId);
		}
		$result->where('section_id','<',$this->middleId);
		
        return $result->get();
	}
	
	public function getSurveySectionNeedUpdate($limit = 0){
		$result = DB::table($this->table.' as r')
				->join('outbound_survey_sections as ss','ss.section_id', '=','r.section_id')
                ->select('r.section_id')
				->whereRaw('ss.section_date_modified > r.updated_at');
		if($limit > 0){
			$result->limit($limit);
		}
		$result->where('ss.section_date_modified', '<',$this->dateRunCronUpdate);
        return $result->get();
		
	}
	
	public function getSurveySectionNeedUpdateNow($limit = 0){
		$result = DB::table($this->table.' as r')
				->join('outbound_survey_sections as ss','ss.section_id', '=','r.section_id')
                ->select('r.section_id')
				->whereRaw('ss.section_date_modified > r.updated_at');
		if($limit > 0){
			$result->limit($limit);
		}
		$result->where('ss.section_date_modified', '>=',$this->dateRunCronUpdate);
        return $result->get();
	}
    
    public function getNullImprovementNote($limit = 0){
        $result = DB::table($this->table.' as r')
                ->join('outbound_survey_result as sr','sr.survey_result_section_id', '=','r.section_id')
                ->select('r.section_id', 'sr.survey_result_note')
                ->whereRaw('r.nps_improvement_note IS NULL AND sr.survey_result_question_id IN (5,7)');
        if($limit > 0){
			$result->limit($limit);
		}
        return $result->get();
    }
}
