<?php

namespace App\Jobs;

use App\Component\HelpProvider;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PushNotification;
use Exception;
use App\Models\ListEmailSale;
use App\Models\ListEmailSir;

class ReSendNotificationEmail extends Job implements ShouldQueue
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
        $this->input['paramMail'] = json_decode($input['push_notification_param'],1);
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
        $modelListEmailSale = new ListEmailSale();
        $modelListEmailSir = new ListEmailSir();
        $model_notification = new PushNotification();
        $modelHelper = new HelpProvider();
        $date = date('Y-m-d H:i:s');
        if ($this->attempts() >= 2) {
            return;
        }

        //Gọi api của sale, net tùy trường hợp
        switch($this->input['api_type']){
            case 'Sale':
                $paramSale['saleMan'] = strtolower($this->input['paramMail']['saleMan']);
                $resSale = $modelListEmailSale->getListEmail($paramSale);
                if(empty($resSale)){
                    $resSale = [
                        'Email' => 'tuyn.huynh@opennet.com.kh',
                        'AccountConfirm' => 'tuyn.huynh'
                    ];
                }
                $res['error'] = false;
                $res['output'] = json_encode($resSale);
                $res['msg'] = 'Có list mail';

                break;
            case 'Tech':
                $paramTech['team'] = strtolower($this->input['paramMail']['team']);
                $resTech = $modelListEmailSir->getListEmail($paramTech);
                if(empty($resTech)){
                    $resTech = [
                        'Email' => 'viet.tran@opennet.com.kh; ngan.nguyen@opennet.com.kh',
                        'AccountConfirm' => 'viet.tran; ngan.nguyen;'
                    ];
                }
                $res['error'] = false;
                $res['output'] = json_encode($resTech);
                $res['msg'] = 'Có list mail';
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
        $param['api_last_sent_at'] = $date;
        $param['api_output'] = $res['output'];
        $param['api_message'] = $res['msg'];
        $param['api_send_count'] = $this->input['api_send_count'] + 1;

        //Cập nhật lại push_notification
        $model_notification->updatePushNotificationOnSendNotification($param);

        switch($this->input['api_type']){
            case 'Sale':
                if(!$res['error']){
                    $type = 'Sale';
                    $tempRes = json_decode($res['output'],1);
                    $mail = explode(';', $tempRes['Email']);
                    foreach($mail as $key => $value){
                        if(empty($value)){
                            unset($mail[$key]);
                        }
                    }
                    $realCc = [
                        'tuyn.huynh@opennet.com.kh',
                        'pheak.hean@opennet.com.kh',
                        'sreypich.chat@opennet.com.kh',
                        'sreyda.lee@opennet.com.kh',
                        'thuy.nguyen@opennet.com.kh'
                    ];

                    try{
                        $modelHelper->sendMail($this->input, $mail, $type, $realCc);
                    }catch(Exception $ex){
                        $param['push_notification_note'] = $ex->getMessage();
                    }
                }
                break;
            case 'Tech':
                if(!$res['error']){
                    $type = 'Tech';
                    $tempRes = json_decode($res['output'],1);
                    $mail = explode(';', $tempRes['Email']);
                    foreach($mail as $key => $value){
                        if(empty($value)){
                            unset($mail[$key]);
                        }
                    }
                    $realCc = [
                        'tuyn.huynh@opennet.com.kh',
                        'pheak.hean@opennet.com.kh',
                        'sreypich.chat@opennet.com.kh',
                        'sreyda.lee@opennet.com.kh',
                        'thuy.nguyen@opennet.com.kh'
                    ];
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

        //Cập nhật lại push_notification
        $model_notification->updatePushNotificationOnSendNotification($param);
	}
}
