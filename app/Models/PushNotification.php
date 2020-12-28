<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PushNotification extends Model {

    protected $table = 'push_notification';
	protected $primary_key = 'push_notification_id';
	protected $fillable = [
		'api_input', 'api_status', 'api_type', 'api_created_at', 
		'api_output', 'api_message', 'api_send_count', 'api_is_reSend',
		'api_last_sent_at',
		
		'confirm_code', 'confirm_user', 'confirm_note', 'confirmed_at',
		
		'push_notification_section_code','push_notification_num_type', 'push_notification_point', 
		'push_notification_note', 'push_notification_template_mail', 'push_notification_param', 
		'push_notification_send_to','push_notification_send_cc', 'push_notification_subjects',
	];
	
	public function getPushNotificationOnConfirmCode($code){
		$result = DB::table($this->table)
                ->select('*')
                ->where('confirm_code', '=', $code)
                ->first();
        return $result;
    }

    public function updateNotificationOutDate() {
        DB::table($this->table)
                ->where('api_send_count', '>', '10')
                ->whereRaw('push_notification_send_to is null')
                ->update(['api_is_reSend' => 0]);
    }

    public function getPushNotificationSendMailAgain() {
        $date = date_create(date("Y-m-d H:i:s"));
        date_add($date, date_interval_create_from_date_string("-10 days"));
        $finalDate = date_format($date, "Y-m-d H:i:s");

        $dateNow = date_create(date("Y-m-d H:i:s"));
        date_add($dateNow, date_interval_create_from_date_string("-90 minutes"));
        $needDate = date_format($dateNow, "Y-m-d H:i:s");
        $result = DB::table($this->table)
                ->select('*')
                ->whereRaw('confirm_code is not null')
                ->where('api_is_reSend', '=', '1')
                ->where('api_created_at', '>', $finalDate)
                ->where('api_last_sent_at', '<', $needDate)
                ->get();
        return $result;
    }

    public function insertPushNotification($input, $paramMail) {
        $resIns = DB::table($this->table)->insert([
            'api_input' => json_encode($input),
            'api_status' => 0,
            'api_type' => $paramMail['sale_net_type'],
            'api_created_at' => $paramMail['date'],
            'api_output' => null,
            'api_message' => 'Processing',
            'confirm_code' => $paramMail['confirm_code'],
            'api_is_reSend' => 1,
            'push_notification_section_code' => $paramMail['code'],
            'push_notification_num_type' => $paramMail['num_type'],
            'push_notification_point' => $paramMail['point'],
            'push_notification_note' => $paramMail['note'],
            'push_notification_param' => json_encode($paramMail),
        ]);
        return $resIns;
    }

    public function getPushNotificationToCheckDuplicate($param) {
        $sql = DB::table($this->table)
                ->select('*');
        if(isset($param['code'])){
            $sql->where("push_notification_section_code", '=', $param['code']);
        }
        if(isset($param['num_type'])){
            $sql->where("push_notification_num_type", '=', $param['num_type']);
        }
        if(isset($param['point'])){
            $sql->where("push_notification_point", '=', $param['point']);
        }
        if(isset($param['sale_net_type'])){
            $sql->where('api_type', '=', $param['sale_net_type']);
        }
        $res = $sql->first();
        return $res;
    }

    public function updatePushNotificationOnSendNotification($param) {
        $res = DB::table($this->table)
                ->where('confirm_code', $param['confirm_code'])
                ->update($param);
        return $res;
    }

    public function updatePushNotificationOnSendNotificationOnId($param) {
        $res = DB::table($this->table)
                ->where('push_notification_id', $param['push_notification_id'])
                ->update($param);
        return $res;
    }

    public function updatePushNotificationOnReSendNotification($param) {
        $res = DB::table($this->table)
                ->where('push_notification_id', $param['id'])
                ->update(['api_status' => $param['status'],
            'api_output' => $param['output'], 'api_send_count' => $param['send_count'],
            'api_message' => $param['message']
                ]
        );
        return $res;
    }

    public function updatePushNotificationOnConfirmNotification($param) {
        $res = DB::table($this->table)
                ->where('confirm_code', $param['confirm_code'])
                ->update($param);
        return $res;
    }

    public function updateNoteNotification($param) {
        $res = DB::table($this->table)
                ->where('confirm_code', $param['confirm_code'])
                ->update($param);
        return $res;
    }

}
