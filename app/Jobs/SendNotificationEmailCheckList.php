<?php

namespace App\Jobs;

use App\Component\HelpProvider;
use App\Models\Api\ApiTele;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Api\ApiMobi;
use App\Models\Api\ApiSale;
use App\Models\PushNotification;

class SendNotificationEmailCheckList extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
	
	protected $input;
	
    public function __construct($input)
    {
		$this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
    */
    public function handle()
    {
        $this->sendAPI();
    }
	
	private function sendAPI(){
		$mobi = new ApiMobi();

		$model_notification = new PushNotification();
		$modelHelper = new HelpProvider();
		$date = date('Y-m-d H:i:s');

        $res = $mobi->pushNotificationToISCGetEmailList($this->input['cl']);
        if(!$res['error']){
            $type = 'CL';
            $mail = $res['output']['msg']['email'];
            $cc = $res['output']['msg']['ccemail'];

            $modelHelper->sendMail($this->input, $mail, $type, $cc);
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
		}
		//Cập nhật lại push_notification
		$model_notification->updatePushNotificationOnSendNotification($param);
	}
}
