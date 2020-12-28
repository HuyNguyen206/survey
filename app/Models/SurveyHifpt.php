<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SurveySections;
use App\Models\SurveyResult;
use DB;

class SurveyHifpt extends Model {
    protected $table = 'survey_hifpt';
    protected $primaryKey = 'id';
    
    public function insertSurveyHifpt($param){
		$resIns = DB::table($this->table)->insert($param);
		return $resIns;
	}
    
    public function insertRatingHifpt($param){
        $info = SurveySections::where(['section_report_id' => $param['section_report_id']])->first();
        if($info === null){
            $id = DB::table('outbound_survey_sections')->insertGetID($param);
        } else {
            $id = $info['section_id'];
            
            foreach($param as $key => $val){
                $info[$key] = $val;
            }
            $info->save();
        }
        return $id;
    }
    
    public function insertRatingResultHifpt($param){
        $info = SurveyResult::where(['survey_result_section_id' => $param['survey_result_section_id']])->first(); 
        if($info === null){
            $res = DB::table('outbound_survey_result')->insert($param);
        } else {
            foreach($param as $key => $val){
                $info->$key = $val;
            }
            $res = $info->save();
        }
        return $res;
    }
}

