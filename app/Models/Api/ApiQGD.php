<?php

/*
 * thực hiện kết nối tới api Tele
 */
namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use App\Component\ExtraFunction;

class ApiQGD extends Model {
	private $push_hipri = 'http://hi-pri.fpt.vn/api/Contract/getEmailLeader';
	
	public function pushNotificationToISCGetEmailList($input){
		$extra = new ExtraFunction();
		$url = $this->push_hipri;
		$resCall = $extra->sendRequest($url, $extra->getHeader(), 'POST',$input);
		$res = [];
		if($resCall['error']){
			$res['error'] = true;
			$res['msg'] = 'Không gọi được api';
			$res['output'] = null;
		}else{
			if($resCall['msg']['code'] != 0){
				$res['error'] = true;
				$res['msg'] = $resCall['msg']['description'];
				$res['output'] = json_encode($resCall);
			}else{
                $res['error'] = true;
                $res['msg'] = $resCall['msg']['description'];
                if(!empty($resCall['msg']['data'])){
                    $res['error'] = false;
                    $res['msg'] = 'Gọi api QGD thành công';
                }
                $res['output'] = json_encode($resCall);
			}
		}
		
		return $res;
	}
}