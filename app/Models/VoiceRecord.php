<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VoiceRecord extends Model
{
    protected $table = 'outbound_voice';
    protected $fillable = ['voice_survey_sections_id', 'voice_records', 'voice_section_time_start'];
	
	public function getAllRecordOnInputInServerVoiceSG($input){
		$voice = DB::connection('mysql_voice_sg')
				->table('cdr as c')
				->join('cdr_next as n','c.ID','=','n.cdr_ID')
				->where('c.calldate','>=',$input['time_from'])
				->where('c.calldate','<=',$input['time_to'])
				->where('c.duration','>',0)
				->orderBy('c.calldate', 'DESC')
				->limit('10')
				->select('c.calldate', 'c.called', 'n.fbasename');
		if(!empty($input['phone'])){
			$voice->where('c.called','=',trim($input['phone']));
		}
		return $voice->get();
	}
	
	public function getAllRecordOnInputInServerVoiceHN($input){
		$voice = DB::connection('mysql_voice_hn')
				->table('cdr as c')
				->join('cdr_next as n','c.ID','=','n.cdr_ID')
				->where('c.calldate','>=',$input['time_from'])
				->where('c.calldate','<=',$input['time_to'])
				->where('c.duration','>',0)
				->orderBy('c.calldate', 'DESC')
				->limit('10')
				->select('c.calldate', 'c.called', 'n.fbasename');
		if(!empty($input['phone'])){
			$voice->where('c.called','=',trim($input['phone']));
		}
		return $voice->get();
	}
}