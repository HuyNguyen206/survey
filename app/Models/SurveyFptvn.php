<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SurveyFptvn extends Model {
    protected $table = 'survey_fptvn';
    protected $primaryKey = 'id';
    
    public function insertSurveyFptvn($param){
		$resIns = DB::table($this->table)->insert($param);
		return $resIns;
	}
}

