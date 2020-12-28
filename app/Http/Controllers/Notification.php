<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushNotification;
use Illuminate\Support\Facades\Auth;
use App\Component\ExtraFunction;
use App\Component\HelpProvider;

class Notification extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
	
	public function confirmView(Request $request, PushNotification $model_push){
		$input = $request->all();
		$help = new HelpProvider();
		if(isset($input['code'])){
			$resPush = $model_push->getPushNotificationOnConfirmCode($input['code']);
			if(empty($resPush)){
					return view('notification/error', [
					'warning' => 'Không tìm thấy mã xác nhận'
				]);
			}
			
			$user_mail = Auth::user()->email;
			$confirm = false;
			
			if(isset($resPush->push_notification_inside_confirm)){
				$user_need_check[0] = $resPush->push_notification_inside_confirm;
			}else{
				$user_need_check = explode('@', $resPush->push_notification_send_to);
			}
			$resCheck = $help->checkConfirmEmail($user_need_check[0], $user_mail);
			if($resCheck && empty($resPush->confirm_user)){
				$confirm = true;
			}
			
			$template = json_decode($resPush->push_notification_param, 1);
			$mail = view('emails.sendNotification', ['param' => $template]);
			
			$pos = strpos($mail, '<a href=');
			if($pos){
				$temp = str_split($mail, $pos);
				$mail = $temp[0];
			}
		}
		else{
			return view('notification/error', [
				'warning' => 'Không tìm thấy mã xác nhận'
			]);
		}
		return view('notification/confirm',[
			'mail' => $mail,
			'code' => $input['code'],
			'queue' => $resPush,
			'confirm' => $confirm,
		]);
	}
	
	public function confirm(Request $request){
		$input = $request->all();
		$help = new HelpProvider();
		if(isset($input['code'])){
			try {
				$model_push = new PushNotification();
				$resPush = $model_push->getPushNotificationOnConfirmCode($input['code']);
				
				if(!empty($resPush)){
					$param['confirm_code'] = $input['code'];
					$param['confirm_note'] = NULL;
					$param['confirmed_at'] = date('Y-m-d H:i:s');
					
					$user = Auth::user();
					$user_mail = $user->email;
					
					if(isset($resPush->push_notification_inside_confirm)){
						$user_need_check[0] = $resPush->push_notification_inside_confirm;
					}else{
						$user_need_check = explode('@', $resPush->push_notification_send_to);
					}
					$user_name = $user_need_check[0];
					$resCheck = $help->checkConfirmEmail($user_need_check[0], $user_mail);
					if(!$resCheck){
						$request->session()->flash('fail', 'Bạn không phải là người cần xác nhận');
						return redirect(url('confirm-notification?code='.$input['code']));
					}
					$param['confirm_user'] = $user_name;
					$param['api_is_reSend'] = 0;
					
					//Cập nhật thông tin push_notification đã nhận được
					$resUp = $model_push->updatePushNotificationOnConfirmNotification($param);
					if($resUp){
						$request->session()->flash('success', 'Xác nhận thành công');
						return redirect(url('confirm-notification?code='.$input['code']));
					}else{
						$request->session()->flash('fail', 'Xác nhận không thành công');
						return redirect(url('confirm-notification?code='.$input['code']));
					}
				}else{
					return view('notification/error', [
						'warning' => 'Không tìm thấy mã xác nhận'
					]);
				}
			} catch (Exception $e) {
				return view('notification/error', [
					'warning' => 'Không tìm thấy mã xác nhận'
				]);
			}
		}else{
			return view('notification/error', [
				'warning' => 'Không tìm thấy mã xác nhận'
			]);
		}
	}
}
