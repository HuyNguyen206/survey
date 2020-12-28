<?php

namespace App\Http\Controllers\Records;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SurveySections;
use App\Component\HelpProvider;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\VoiceRecord;
use Illuminate\Support\Facades\Response;

class VoiceRecords extends Controller {

	public function getVoiceRecords(Request $request,$id) {
		$help = new HelpProvider();
		$modelRecord = new VoiceRecord();
		$input = $request->all();
		
		if(!isset($input['time_from'])){
			$input['time_from'] = date('Y-m-d 00:00:00');
		}
		if(!isset($input['time_to'])){
			$input['time_to'] = date('Y-m-d 23:59:59');
		}

		$resValid = $help->validateDateStartEnd($input['time_from'], $input['time_to']);
		if(!$resValid){
			$input['time_from'] = date('Y-m-d 00:00:00');
			$input['time_to'] = date('Y-m-d 23:59:59');
		}else{
			$input['time_from'] = date_format($input['time_from'], 'Y-m-d H:i:s');
			$input['time_to'] = date_format($input['time_to'], 'Y-m-d H:i:s');
		}

		$input['phone'] = $id;
		$resVoice = $modelRecord->getAllRecordOnInputInServerVoice($input);
		
		return view('records/voiceRecords', [
			'voice' => $resVoice,
			'input' => $input
		]);
	}

	public function searchVoiceRecords(Request $request) {
		$help = new HelpProvider();
		$modelRecord = new VoiceRecord();
		$input = $request->all();
		
		$resValid = $help->validateDateStartEnd($input['time_from'], $input['time_to']);
		if(!$resValid){
			$input['time_from'] = date('Y-m-d 00:00:00');
			$input['time_to'] = date('Y-m-d 23:59:59');
		}else{
			$input['time_from'] = date_format($input['time_from'], 'Y-m-d H:i:s');
			$input['time_to'] = date_format($input['time_to'], 'Y-m-d H:i:s');
		}
		
		$resVoice = $modelRecord->getAllRecordOnInputInServerVoice($input);
		
		if(!empty($input['phone'])){
			if(count($resVoice) >= 10){
				$request->session()->flash('alert', 'Bạn cần giới hạn thời gian ngắn hơn để kết quả được chính xác hơn');
				return redirect(url('dashboard/get-voice-records/'.trim($input['phone']).'/?time_from='.$input['time_from'].'&time_to='.$input['time_to']));
			}
		}
		
		return view('records/voiceRecords', [
			'voice' => $resVoice,
			'input' => $input
		]);
	}
	
	public function getVoiceRecordsAjax(Request $request) {
		$modelRecord = new VoiceRecord();
		$input = $request->all();
		
		try{
			//Kiểm tra xem có tồn tại survey section id hay không
			$section = SurveySections::find($input['id']);
			if(empty($section)){
				return Response::json(array('state' => 'fail', 'error' => 'Không tìm thấy Mã khảo sát'));
			}
			
			//Kiểm tra xem đã có người nghe qua record này hay không
			$record = VoiceRecord::where([
				'voice_survey_sections_id' => $input['id'], 
				'voice_section_time_start' => $section->section_time_start
			])->first();
			if(!empty($record)){
				$result = json_decode($record->voice_records);
				return Response::json($result);
			}
			
			$phone = $section->section_contact_phone;
			if(!empty($phone)){
				//Tăng khoảng thời gian tìm kiếm lên trước 30' và sau 30' so với thời điểm bắt đầu khảo sát
				$all_records = [];
				$date = date_create($section->section_time_start);
				$date->modify("-30 minutes");
				$input['time_from'] = date_format($date, 'Y-m-d H:i:s');
				$date->modify("+60 minutes");
				$input['time_to'] = date_format($date, 'Y-m-d H:i:s');
				
				$templateUrlSG = 'http://118.69.241.36/media/%s/AUDIO/%s.mp3';
				$templateUrlHN = 'http://118.70.0.62/media/%s/AUDIO/%s.mp3';
				//Tìm các cuộc ghi âm với số điện thoại được tìm thấy
				$input['phone'] = $phone;
				//Lấy toàn bộ các cuộc ghi âm
				$resVoice = $modelRecord->getAllRecordOnInputInServerVoiceSG($input);
				if(!empty($resVoice)){
					foreach($resVoice as $record){
						//Tạo link đến file ghi âm
						$date = date('Y-m-d/H/i',strtotime($record->calldate));
						$temp['date'] = date('d-m-Y H:i:s',strtotime($record->calldate));
						$temp['url'] = sprintf($templateUrlSG, $date, $record->fbasename);
						$temp['phone'] = $input['phone'];
						//Tập trung các cuộc ghi âm của nhiều số điện thoại vào một nơi
						array_push($all_records, $temp);
					}
				}else{
					$resVoice = $modelRecord->getAllRecordOnInputInServerVoiceHN($input);
					if(!empty($resVoice)){
						foreach($resVoice as $record){
							//Tạo link đến file ghi âm
							$date = date('Y-m-d/H/i',strtotime($record->calldate));
							$temp['date'] = date('d-m-Y H:i:s',strtotime($record->calldate));
							$temp['url'] = sprintf($templateUrlHN, $date, $record->fbasename);
							$temp['phone'] = $input['phone'];
							//Tập trung các cuộc ghi âm của nhiều số điện thoại vào một nơi
							array_push($all_records, $temp);
						}
					}
				}
				
				//Đếm số lượng file ghi âm
				$count = count($all_records);
				if($count == 0){
					$result = ['state' => 'fail', 'error' => 'Không tìm thấy cuộc ghi âm nào'];
				}else{
					$view = $this->getTableRecordView($all_records);
					$result = ['state' => 'success', 'count' => $count,'detail' => $view];
					
					$modelRecord->voice_survey_sections_id = $input['id'];
					$modelRecord->voice_records = json_encode($result);
					$modelRecord->voice_section_time_start = $section->section_time_start;
					$modelRecord->save();
				}
			}else{
				$result = ['state' => 'fail', 'error' => "Không tìm thấy bất kỳ số điện thoại liên hệ nào"];
			}
			
			
			return Response::json($result);
		}catch(Exception $e){
//			return Response::json(array('state' => 'fail', 'error' => $e->getMessage()));
			return Response::json(array('state' => 'fail', 'error' => 'Lỗi xảy ra trên hệ thống'));
		}
	}
	
	private function getTableRecordView($all_records){
		$templateViewTable = '<table class="table table-striped table-bordered table-hover">'
			. '<thead>'
			.	'<tr>'
			.		'<th class="center">STT</th>'
			.		'<th><i class="icon-time bigger-120"></i>Ngày ghi âm</th>'
			.		'<th><i class="icon-phone bigger-120"></i>Số điện thoại</th>'
			.		'<th>Hành động</th>'
			.	'</tr>'
			. '</thead>'
			. '<tbody>%s</tbody>'
			. '</table>';
		
		$templatePlus = '';
		$templateViewOne = '<audio class="audio_class" controls autoplay>'
			.	'<source src="%s" type="audio/mp3">'
			.	'</audio>';
		
		$templateViewMany = '<tr>'
			.	'<td class="center">%d</td>'
			.	'<td>%s</td>'
			.	'<td>%s</td>'
			.	'<td>'
			.		'<audio class="audio_class" controls id="audio_control_%d" style="width: 45px;">'
			.			'<source src="%s" type="audio/mp3">'
			.		'</audio>'
			.	'</td>'
			. '</tr>';
		
		$count = count($all_records);
		
		if($count == 1){
			$view = sprintf($templateViewOne,$all_records[0]['url']);
		}else{
			foreach($all_records as $key => $record){
				$templatePlus .= sprintf($templateViewMany, $key + 1, $record['date'],$record['phone'], $key + 1,$record['url']);
			}
			$view = sprintf($templateViewTable, $templatePlus);
		}
		
		return $view;
	}
}
