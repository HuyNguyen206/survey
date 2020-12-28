<?php

/*
 * thực hiện kết nối tới api Sale
 */
namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use App\Component\ExtraFunction;

class ApiSale extends Model {
	private $push_sale = 'http://customercare.fpt.net/CustomerCare.svc/PushInfoForSale';
	
	public function pushNotificationToSale($input){
		$extra = new ExtraFunction();
		$resCall = $extra->sendRequest($this->push_sale, $extra->getHeader(), 'POST',$input);
		$res = [];
		if($resCall['error']){
			$res['error'] = true;
			$res['msg'] = 'Không gọi được api';
			$res['output'] = null;
		}else{
			if($resCall['msg']['RESPONSE_OBJECT']['ErrorCode'] !== 0){
				$res['error'] = true;
				$res['msg'] = $resCall['msg']['RESPONSE_OBJECT']['Error'];
				$res['output'] = json_encode($resCall);
			}else{
				$res['msg'] = $resCall['msg']['RESPONSE_OBJECT']['ListObject'][0]['Message'];
				$res['output'] = json_encode($resCall);
				$res['error'] = true;
				if($resCall['msg']['RESPONSE_OBJECT']['ListObject'][0]['Code'] > 0){
					$res['error'] = false;
				}
			}
		}
		
		return $res;
	}
}