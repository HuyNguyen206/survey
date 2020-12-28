<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Api\ApiSale;
use App\Models\PushNotification;

class SendNotificationMobile extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $input;

    public function __construct($input) {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
    */
    public function handle()
    {
        $typeSend = ['sale'];
		//Xét tất cả các trường hợp gửi api cho ISC
		foreach($typeSend as $type){
			if(!empty($this->input[$type])){
				//Kiểm tra xem có gửi trùng lặp hay không
				$iSsend = $this->isSendAPI($this->input['paramMail']);
				if($iSsend){
					$this->sendAPI($type);
				}
			}
		}
    }

    private function sendAPI($type){
        $sale = new ApiSale();
        $model_notification = new PushNotification();
        $date = date('Y-m-d H:i:s');
        if ($this->attempts() >= 2) {
            return;
        }
        //Gọi api của sale, net tùy trường hợp
        switch($type){
            case 'sale':
                $res = $sale->pushNotificationToSale($this->input[$type]);
                break;
            default:
                return;
        }

        if($res['error']){
            $status = 0;
        }else{
            $status = 1;
        }

        $param['confirm_code'] = $this->input['paramMail']['confirm_code'];
        $param['api_status'] = $status;
        $param['api_created_at'] = $date;
        $param['api_last_sent_at'] = $date;
        $param['api_output'] = $res['output'];
        $param['api_message'] = $res['msg'];
        $param['api_send_count'] = 1;

        if($status){
            $temp = json_decode($param['api_output'],1);

            //Bổ sung thông tin mail đối với trường hợp gọi api thành công
            if(isset($temp['msg']['email'])){
                $param['push_notification_send_to'] = $temp['msg']['email'];
            }
            if(isset($temp['msg']['ccemail'])){
                $param['push_notification_send_cc'] = $temp['msg']['ccemail'];
            }
            if(isset($temp['msg']['accountinside'])){
                $param['push_notification_inside_confirm'] = strtolower($temp['msg']['accountinside']);
            }

            // Bổ sung thông tin mail đối với trường hợp send mail
            if(isset($temp['msg']['data'][0]['EmailLeader'])){
                $param['push_notification_send_to'] = $temp['msg']['data'][0]['EmailLeader'];
            }
            if(isset($temp['msg']['data'][0]['AccountInsideLeader'])){
                $param['push_notification_inside_confirm'] = strtolower($temp['msg']['data'][0]['AccountInsideLeader']);
            }

            //Bổ sung thông tin đối với trường hợp send mail qgd
            if(isset($temp['email_list_to'])){
                $param['push_notification_send_to'] = $temp['email_list_to'];
            }
        }

        //Cập nhật lại push_notification
        $model_notification->updatePushNotificationOnSendNotification($param);
    }
	
	private function isSendAPI($param){
		$model_notification = new PushNotification();
		$out = $model_notification->getPushNotificationToCheckDuplicate($param);
		if(count($out) < 1){
			$send = false;
		}else{
			$send = true;
			if($out->confirm_code != $param['confirm_code']){
				$send = false;
			}
		}
		return $send;
	}
}
