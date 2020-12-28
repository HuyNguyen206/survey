<?php

namespace App\Jobs;

use App\Component\HelpProvider;
use App\Models\Api\ApiTele;
use App\Models\ListEmailCUS;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Api\ApiMobi;
use App\Models\Api\ApiSale;
use App\Models\PushNotification;
use App\Models\ListEmailQGD;

class SendNotificationEmail extends Job implements ShouldQueue
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
        $typeSend = ['sale', 'tech', 'tele', 'cl', 'qgd', 'cus'];
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
        $mobi = new ApiMobi();
        $tele = new ApiTele();
        $modelListEmailQGD = new ListEmailQGD();
        $modelListEmailCUS = new ListEmailCUS();
        $tempCUS = false;
        $model_notification = new PushNotification();
        $modelHelper = new HelpProvider();
        $date = date('Y-m-d H:i:s');
        if ($this->attempts() >= 2) {
            return;
        }
        //Gọi api của sale, net tùy trường hợp
        switch($type){
            case 'sale':
                $res = $sale->pushNotificationToSale($this->input[$type]);
                break;
            case 'tech':
//                $res = $mobi->pushNotificationToNet($this->input[$type]);
//                $param['push_notification_subjects'] = $this->input['paramMail']['subject'];
                $res = $mobi->pushNotificationToISCGetEmailList($this->input[$type]);
                break;
            case 'tele':
                $res = $tele->pushNotificationToISCGetEmailList($this->input[$type]);
                break;
            case 'cl':
                $res = $mobi->pushNotificationToISCGetEmailList($this->input[$type]);
                break;
            case 'qgd':
                $paramQGD['location_id'] = $this->input['paramMail']['location_id'];
                $paramQGD['branch_code'] = $this->input['paramMail']['branch_code'];
                $resQGD = $modelListEmailQGD->getListEmail($paramQGD);
                $res['error'] = false;
                $res['output'] = json_encode($resQGD);
                $res['msg'] = 'Có list mail';
                if(empty($resQGD)){
                    $res['error'] = true;
                    $res['msg'] = 'Không có list mail';
                }
                break;
            case 'cus':
                $paramCUS['location_id'] = $this->input['paramMail']['location_id'];
                $paramCUS['branch_code'] = $this->input['paramMail']['branch_code'];
                $resCUS = $modelListEmailCUS->getListEmail($paramCUS);
                $res['error'] = false;
                $res['output'] = json_encode($resCUS);
                $res['msg'] = 'Có list mail';
                if(empty($resCUS)){
                    $tempCUS = true;
                }
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

        switch($type){
            case 'sale':
                break;
            case 'tech':
                if(!$res['error']){
                    $type = 'Tech';
                    $tempRes = json_decode($res['output'],1);
                    $mail = $tempRes['msg']['email'];
                    $cc = $tempRes['msg']['ccemail'];
                    $realCc = [];
                    $temp = explode(';', $cc);
                    foreach($temp as $val){
                        if(!empty($val)){
                            array_push($realCc, $val);
                        }
                    }
                    try{
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    }catch(Exception $ex){
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'tele':
                if(!$res['error']){
                    $type = 'Tele';
                    $tempRes = json_decode($res['output'],1);
                    $mail = $tempRes['msg']['data'][0]['EmailLeader'];
                    $realCc = [];
                    try{
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    }catch(Exception $ex){
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'cl':
                if(!$res['error']){
                    $type = 'CL';
                    $tempRes = json_decode($res['output'],1);
                    $mail = $tempRes['msg']['email'];
                    $cc = $tempRes['msg']['ccemail'];
                    $realCc = [];
                    $temp = explode(';', $cc);
                    foreach($temp as $val){
                        if(!empty($val)){
                            array_push($realCc, $val);
                        }
                    }
                    try{
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    }catch(Exception $ex){
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'qgd':
                if(!$res['error']){
                    $type = 'QGD';
                    $tempRes = json_decode($res['output'],1);
                    $mail = explode(';', $tempRes['email_list_to']);
                    $realCc = explode(';', $tempRes['email_list_cc']);

                    try{
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    }catch(Exception $ex){
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'cus':
                if(!$res['error']){
                    $type = 'CUS';
                    $tempRes = json_decode($res['output'],1);
                    if($tempCUS){
                        $mail = [
                            'anhdv4@fpt.com.vn',
                            'hantp@fpt.com.vn',
                            'toannm@fpt.com.vn',
                            'phutm@fpt.com.vn',
                        ];
                        $realCc = [
                        ];
                    }else{
                        $mail = explode(';', $tempRes['email_list']);
                        $realCc = [
                            'anhdv4@fpt.com.vn',
                            'hantp@fpt.com.vn',
                            'toannm@fpt.com.vn',
                            'phutm@fpt.com.vn',
                        ];
                    }
                    try{
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    }catch(Exception $ex){
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            default:
                return;
        }
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
