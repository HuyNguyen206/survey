<?php

/*
 * thực hiện kết nối tới api Tele
 */
namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use App\Component\ExtraFunction;

class ApiTele extends Model {
	private $push_tele = 'http://telesalesapi.fpt.vn/api/Telesales/GetInfoChecklistTLS';
	
	public function pushNotificationToISCGetEmailList($input){
		$extra = new ExtraFunction();
		$url = $this->push_tele . '?Contract='.$input['Contract'];
		$resCall = $extra->sendRequest($url, $extra->getHeader(), 'GET',$input);
		$res = [];
		if($resCall['error']){
			$res['error'] = true;
			$res['msg'] = 'Không gọi được api';
			$res['output'] = null;
		}else{
			if($resCall['msg']['result'] != 1){
				$res['error'] = true;
				$res['msg'] = $resCall['msg']['message'];
				$res['output'] = json_encode($resCall);
			}else{
                $res['error'] = true;
                $res['msg'] = $resCall['msg']['message'];
                if($resCall['msg']['total'] > 0){
                    $res['error'] = false;
                    $res['msg'] = 'Gọi api telesale thành công';
                }
                $res['output'] = json_encode($resCall);
			}
		}
		
		return $res;
	}
}