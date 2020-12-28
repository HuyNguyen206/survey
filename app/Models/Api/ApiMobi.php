<?php

/*
 * thực hiện kết nối tới api MobiNet
 */

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use App\Component\ExtraFunction;

class ApiMobi extends Model {
    private $push_mobi = 'http://beta.wsmobinet.fpt.vn/MobiNet.svc/PushWarningEmail';
	private $push_mobi_subject = 'http://beta.wsmobinet.fpt.vn/MobiNet.svc/PushWarningEmailWithSubject';
	private $pust_to_get = 'http://beta.wsmobinet.fpt.vn/MobiNet.svc/PushCemEmails';
	
    public function pushNotificationToNet($input) {
        $extra = new ExtraFunction();
        $resCall = $extra->sendRequest($this->push_mobi_subject, $extra->getHeader(), 'POST', $input);
        $res = [];
        if ($resCall['error']) {
            $res['error'] = true;
            $res['msg'] = 'Không gọi được api';
            $res['output'] = null;
        } else {
            $res['error'] = false;
            $res['msg'] = 'Gọi api net, gởi email thành công';
            $res['output'] = json_encode($resCall);
            if (isset($resCall['msg']['id'])) {
                if ($resCall['msg']['id'] < 0) {
                    $res['error'] = true;
                    $res['msg'] = 'Gửi email thất bại';
                }
            }
        }

        return $res;
    }

	public function pushNotificationToISCGetEmailList($input){
		$extra = new ExtraFunction();
        $resCall = $extra->sendRequest($this->pust_to_get, $extra->getHeader(), 'POST', $input);
        $res = [];
        if ($resCall['error']) {
            $res['error'] = true;
            $res['msg'] = 'Không gọi được api';
            $res['output'] = null;
        } else {
            $res['error'] = false;
            $res['msg'] = 'Gọi api, lấy danh sách email thành công';
            $res['output'] = json_encode($resCall);
            if (isset($resCall['msg']['id'])) {
                if ($resCall['msg']['id'] < 0) {
                    $res['error'] = true;
                    $res['msg'] = 'Không có email điều hành';
				}
            }
        }

        return $res;
	}
}
